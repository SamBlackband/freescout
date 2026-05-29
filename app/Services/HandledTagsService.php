<?php

namespace App\Services;

use App\Conversation;
use App\HandledTag;
use App\User;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandledTagsService
{
    const DEFAULT_COLOR = '#5B6B7A';
    const TAG_TABLE = 'handled_tags';
    const PIVOT_TABLE = 'handled_conversation_tag';

    public function canManageTags($user = null)
    {
        return $user && ($user->isAdmin() || $user->hasPermission(User::PERM_EDIT_TAGS));
    }

    public function getAllTags()
    {
        return HandledTag::orderBy('name')->get();
    }

    public function getManagementTags()
    {
        return HandledTag::withCount('conversations')->orderBy('name')->get();
    }

    public function getTagOptions()
    {
        return $this->getAllTags()->map(function (HandledTag $tag) {
            return [
                'id' => (int) $tag->id,
                'name' => $tag->name,
                'color' => $tag->color ?: self::DEFAULT_COLOR,
            ];
        })->values()->all();
    }

    public function normalizeTagIds($tagIds)
    {
        $normalized = [];

        foreach ((array) $tagIds as $tagId) {
            if (is_string($tagId) && strpos($tagId, ',') !== false) {
                foreach (explode(',', $tagId) as $nestedTagId) {
                    $nestedTagId = (int) trim($nestedTagId);
                    if ($nestedTagId > 0) {
                        $normalized[] = $nestedTagId;
                    }
                }

                continue;
            }

            $tagId = (int) $tagId;
            if ($tagId > 0) {
                $normalized[] = $tagId;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function getValidTagIds($tagIds)
    {
        $tagIds = $this->normalizeTagIds($tagIds);

        if (!count($tagIds)) {
            return [];
        }

        return HandledTag::whereIn('id', $tagIds)->orderBy('name')->pluck('id')->map(function ($tagId) {
            return (int) $tagId;
        })->values()->all();
    }

    public function findTagsByIds($tagIds)
    {
        $tagIds = $this->getValidTagIds($tagIds);

        if (!count($tagIds)) {
            return collect();
        }

        return HandledTag::whereIn('id', $tagIds)->orderBy('name')->get();
    }

    public function normalizeName($name)
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $name));

        return mb_substr($name, 0, 60);
    }

    public function normalizeColor($color)
    {
        $color = strtoupper(ltrim(trim((string) $color), '#'));

        if (!preg_match('/^[0-9A-F]{3}([0-9A-F]{3})?$/', $color)) {
            return self::DEFAULT_COLOR;
        }

        if (strlen($color) === 3) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }

        return '#'.$color;
    }

    public function createOrUpdateTag(array $data, HandledTag $tag = null)
    {
        $name = $this->normalizeName($data['name'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException(__('Tag name is required'));
        }

        if (
            HandledTag::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->when($tag && $tag->id, function ($query) use ($tag) {
                    $query->where('id', '!=', $tag->id);
                })
                ->exists()
        ) {
            throw new \InvalidArgumentException(__('A tag with this name already exists'));
        }

        if (!$tag) {
            $tag = new HandledTag();
        }

        $tag->name = $name;
        $tag->slug = $this->buildUniqueSlug($name, $tag->id);
        $tag->color = $this->normalizeColor($data['color'] ?? '');
        $tag->save();

        return $tag;
    }

    public function syncConversationTags(Conversation $conversation, $tagIds, $user = null)
    {
        $tagIds = $this->getValidTagIds($tagIds);
        $syncData = [];

        foreach ($tagIds as $tagId) {
            $syncData[$tagId] = [
                'applied_by_user_id' => $user ? $user->id : null,
            ];
        }

        $conversation->handledTags()->sync($syncData);
        $conversation->unsetRelation('handledTags');

        return $tagIds;
    }

    public function addTagToConversations($conversationIds, $tagId, $user = null)
    {
        $conversationIds = array_values(array_unique(array_map('intval', (array) $conversationIds)));
        $tag = HandledTag::find((int) $tagId);

        if (!$tag || !count($conversationIds)) {
            return;
        }

        foreach (Conversation::findMany($conversationIds) as $conversation) {
            $conversation->handledTags()->syncWithoutDetaching([
                $tag->id => ['applied_by_user_id' => $user ? $user->id : null],
            ]);
            $conversation->unsetRelation('handledTags');
        }
    }

    public function removeTagFromConversations($conversationIds, $tagId)
    {
        $conversationIds = array_values(array_unique(array_map('intval', (array) $conversationIds)));
        $tagId = (int) $tagId;

        if (!$tagId || !count($conversationIds)) {
            return;
        }

        DB::table(self::PIVOT_TABLE)
            ->where('tag_id', $tagId)
            ->whereIn('conversation_id', $conversationIds)
            ->delete();
    }

    public function replaceTag(HandledTag $fromTag, HandledTag $toTag, $user = null)
    {
        if ($fromTag->id === $toTag->id) {
            return;
        }

        $now = now();
        $targetConversationIds = DB::table(self::PIVOT_TABLE)
            ->where('tag_id', $toTag->id)
            ->pluck('conversation_id')
            ->map(function ($conversationId) {
                return (int) $conversationId;
            })
            ->all();

        $replaceConversationIds = DB::table(self::PIVOT_TABLE)
            ->where('tag_id', $fromTag->id)
            ->when(count($targetConversationIds), function ($query) use ($targetConversationIds) {
                $query->whereNotIn('conversation_id', $targetConversationIds);
            })
            ->pluck('conversation_id')
            ->map(function ($conversationId) {
                return (int) $conversationId;
            })
            ->all();

        if (count($replaceConversationIds)) {
            DB::table(self::PIVOT_TABLE)
                ->where('tag_id', $fromTag->id)
                ->whereIn('conversation_id', $replaceConversationIds)
                ->update([
                    'tag_id' => $toTag->id,
                    'applied_by_user_id' => $user ? $user->id : null,
                    'updated_at' => $now,
                ]);
        }

        DB::table(self::PIVOT_TABLE)
            ->where('tag_id', $fromTag->id)
            ->delete();
    }

    public function deleteTag(HandledTag $tag)
    {
        $tag->conversations()->detach();
        $tag->delete();
    }

    public function preloadConversationTags($conversations)
    {
        if ($conversations instanceof AbstractPaginator) {
            $conversations->getCollection()->load('handledTags');

            return $conversations;
        }

        if (is_object($conversations) && method_exists($conversations, 'load')) {
            $conversations->load('handledTags');
        }

        return $conversations;
    }

    protected function buildUniqueSlug($name, $ignoreId = null)
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug ?: 'tag';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            HandledTag::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

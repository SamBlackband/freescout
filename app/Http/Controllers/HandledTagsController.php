<?php

namespace App\Http\Controllers;

use App\HandledTag;
use App\Services\HandledTagsService;
use Illuminate\Http\Request;

class HandledTagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function manage(Request $request)
    {
        $user = auth()->user();
        $tagsService = app(HandledTagsService::class);

        abort_unless($tagsService->canManageTags($user), 403);

        $action = $request->input('action');

        try {
            switch ($action) {
                case 'create':
                    $tagsService->createOrUpdateTag($request->only(['name', 'color']));
                    \Session::flash('flash_success', __('Tag created'));
                    break;

                case 'update':
                    $tag = HandledTag::findOrFail((int) $request->input('tag_id'));
                    $tagsService->createOrUpdateTag($request->only(['name', 'color']), $tag);
                    \Session::flash('flash_success', __('Tag updated'));
                    break;

                case 'replace':
                    $tag = HandledTag::findOrFail((int) $request->input('tag_id'));
                    $replacementTag = HandledTag::findOrFail((int) $request->input('replacement_tag_id'));
                    $tagsService->replaceTag($tag, $replacementTag, $user);
                    $tagsService->deleteTag($tag);
                    \Session::flash('flash_success', __('Tag replaced'));
                    break;

                case 'delete':
                    $tag = HandledTag::findOrFail((int) $request->input('tag_id'));
                    $tagsService->deleteTag($tag);
                    \Session::flash('flash_success', __('Tag deleted'));
                    break;

                default:
                    \Session::flash('flash_error', __('Incorrect action'));
                    break;
            }
        } catch (\InvalidArgumentException $e) {
            \Session::flash('flash_error', $e->getMessage());
        }

        return redirect()->route('settings', ['section' => 'tags']);
    }
}

<?php

namespace App\Services;

use App\Conversation;
use App\Option;

class HandledSavedRepliesService
{
    const OPTION_NAME = 'handled_saved_replies';

    public function getStoredReplies()
    {
        $savedReplies = Option::get(self::OPTION_NAME, []);

        return $this->normalizeReplies(is_array($savedReplies) ? $savedReplies : []);
    }

    public function getComposerReplies($conversation = null, $savedReplies = null)
    {
        $storedReplies = is_array($savedReplies) ? $this->normalizeReplies($savedReplies) : $this->getStoredReplies();

        return array_values(array_map(function ($savedReply) use ($conversation) {
            $savedReply['rendered_body'] = $this->renderReplyBody($savedReply['body'], $conversation);

            return $savedReply;
        }, $storedReplies));
    }

    public function saveReplies(array $savedReplies)
    {
        $normalizedReplies = $this->normalizeReplies($savedReplies);

        Option::set(self::OPTION_NAME, $normalizedReplies);
        Option::$cache[self::OPTION_NAME] = $normalizedReplies;

        return $normalizedReplies;
    }

    public function normalizeReplies(array $savedReplies)
    {
        $normalizedReplies = [];

        foreach ($savedReplies as $savedReply) {
            if (!is_array($savedReply)) {
                continue;
            }

            $category = mb_substr(trim((string) ($savedReply['category'] ?? '')), 0, 80);
            $name = mb_substr(trim((string) ($savedReply['name'] ?? '')), 0, 80);
            $body = trim((string) ($savedReply['body'] ?? ''));

            if ($category === '' && $name === '' && $body === '') {
                continue;
            }

            if ($name === '' || $body === '') {
                continue;
            }

            $normalizedReplies[] = [
                'category' => $category,
                'name' => $name,
                'body' => \Helper::stripDangerousTags($body),
            ];
        }

        return array_values($normalizedReplies);
    }

    public function renderReplyBody($body, $conversation = null)
    {
        $renderedBody = trim((string) $body);

        if ($renderedBody === '') {
            return '';
        }

        if ($conversation instanceof Conversation) {
            $renderedBody = $conversation->replaceTextVars($renderedBody);
        }

        return strtr($renderedBody, $this->buildHandledPlaceholderMap($conversation));
    }

    protected function buildHandledPlaceholderMap($conversation = null)
    {
        $customerName = '';
        $customerEmail = '';
        $businessName = '';
        $ownerName = '';
        $businessEmail = '';
        $ticketId = '';

        if ($conversation instanceof Conversation) {
            $customer = $conversation->customer;
            $customerName = $customer ? trim((string) $customer->getFullName(true, true)) : '';
            $customerEmail = trim((string) $conversation->customer_email);
            if (!$customerEmail && $customer) {
                $customerEmail = trim((string) $customer->getMainEmail());
            }

            try {
                $handledSupportContext = app(HandledSupportContextService::class)->lookupForConversation($conversation);
            } catch (\Exception $e) {
                $handledSupportContext = null;
            }

            if (is_array($handledSupportContext)) {
                $handledBusiness = is_array($handledSupportContext['business'] ?? null) ? $handledSupportContext['business'] : [];
                $handledTicket = is_array($handledSupportContext['ticket'] ?? null) ? $handledSupportContext['ticket'] : [];

                $businessName = trim((string) ($handledBusiness['name'] ?? ''));
                $ownerName = trim((string) ($handledBusiness['owner_name'] ?? ''));
                $businessEmail = trim((string) ($handledBusiness['email'] ?? ''));
                $ticketId = trim((string) (($handledTicket['id'] ?? '') ?: ($handledTicket['ticket_id'] ?? '')));
            }
        }

        return [
            '{{handled.business_name}}' => $businessName,
            '{{handled.owner_name}}' => $ownerName,
            '{{handled.business_email}}' => $businessEmail,
            '{{handled.customer_name}}' => $customerName,
            '{{handled.customer_email}}' => $customerEmail,
            '{{handled.ticket_id}}' => $ticketId,
        ];
    }
}

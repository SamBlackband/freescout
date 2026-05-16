<?php

namespace App\Services;

use App\Conversation;
use App\Customer;
use App\Thread;
use GuzzleHttp\Client;

class HandledSupportSyncEmitter
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function emitThreadCreated($thread)
    {
        if (!($thread instanceof Thread) || !$thread->id) {
            return;
        }

        if ((int) $thread->state !== Thread::STATE_PUBLISHED) {
            return;
        }

        $thread = Thread::with([
            'conversation.mailbox',
            'conversation.customer.emails',
            'customer.emails',
            'user',
            'created_by_user',
        ])->find($thread->id) ?: $thread;

        $conversation = $thread->conversation;

        if (!$conversation) {
            \Log::warning('Handled support sync skipped thread.created: conversation missing', [
                'thread_id' => $thread->id,
            ]);

            return;
        }

        $this->postPayload('thread.created', [
            'conversation' => $this->normalizeConversation($conversation),
            'thread' => $this->normalizeThread($thread),
        ], [
            'conversation_id' => $conversation->id,
            'thread_id' => $thread->id,
        ]);
    }

    public function emitConversationUpdated($conversation)
    {
        if (!($conversation instanceof Conversation) || !$conversation->id) {
            return;
        }

        $changes = $conversation->getChanges();

        if (!$this->shouldEmitConversationUpdate($changes)) {
            return;
        }

        $conversation = Conversation::with([
            'mailbox',
            'customer.emails',
            'threads' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            },
        ])->find($conversation->id) ?: $conversation;

        $this->postPayload('conversation.updated', [
            'conversation' => $this->normalizeConversation($conversation),
            'changed_fields' => array_keys($changes),
            'changes' => $this->normalizeChanges($changes),
            'latest_thread' => $this->normalizeLatestThread($conversation),
        ], [
            'conversation_id' => $conversation->id,
        ]);
    }

    protected function shouldEmitConversationUpdate(array $changes)
    {
        if (!$changes) {
            return false;
        }

        $relevantFields = [
            'subject',
            'status',
            'state',
            'type',
            'mailbox_id',
            'customer_id',
            'customer_email',
            'preview',
            'source_type',
            'source_via',
            'last_reply_at',
            'last_reply_from',
            'threads_count',
            'closed_at',
            'folder_id',
            'user_id',
        ];

        return (bool) array_intersect(array_keys($changes), $relevantFields);
    }

    protected function postPayload($eventName, array $payload, array $context = [])
    {
        if (!$this->isEnabled()) {
            return;
        }

        $url = trim((string) config('app.handled_support_sync_url'));
        $secret = (string) config('app.handled_support_sync_secret');

        if (!$url || !$secret) {
            \Log::warning('Handled support sync skipped: missing configuration', $context + [
                'event' => $eventName,
                'url_configured' => (bool) $url,
                'secret_configured' => (bool) $secret,
            ]);

            return;
        }

        $requestPayload = [
            'version' => 1,
            'event' => $eventName,
            'emitted_at' => $this->normalizeTimestamp(now()),
            'source' => [
                'system' => 'handled-freescout',
                'environment' => config('app.env'),
                'app_url' => config('app.url'),
            ],
        ] + $payload;

        try {
            $response = $this->client->post($url, \Helper::setGuzzleDefaultOptions([
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$secret,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'handled-freescout-support-sync/1.0',
                    'X-Handled-Support-Sync-Secret' => $secret,
                ],
                'json' => $requestPayload,
            ]));

            if ($response->getStatusCode() >= 400) {
                \Log::error('Handled support sync request failed', $context + [
                    'event' => $eventName,
                    'status' => $response->getStatusCode(),
                    'response' => substr((string) $response->getBody(), 0, 1000),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Handled support sync request threw exception', $context + [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function isEnabled()
    {
        return (bool) config('app.handled_support_sync_enabled');
    }

    protected function normalizeConversation(Conversation $conversation)
    {
        $customer = $conversation->customer;
        $mailbox = $conversation->mailbox;

        return [
            'id' => $conversation->id,
            'number' => $conversation->number,
            'subject' => $conversation->subject,
            'preview' => $conversation->preview,
            'status' => [
                'id' => $conversation->status,
                'name' => Conversation::$statuses[$conversation->status] ?? null,
            ],
            'state' => [
                'id' => $conversation->state,
                'name' => Conversation::$states[$conversation->state] ?? null,
            ],
            'channel' => [
                'id' => $conversation->type,
                'name' => Conversation::$types[$conversation->type] ?? null,
            ],
            'source' => [
                'type_id' => $conversation->source_type,
                'type_name' => Conversation::$source_types[$conversation->source_type] ?? null,
                'via_id' => $conversation->source_via,
                'via_name' => Conversation::$persons[$conversation->source_via] ?? null,
                'last_reply_from_id' => $conversation->last_reply_from,
                'last_reply_from_name' => Conversation::$persons[$conversation->last_reply_from] ?? null,
            ],
            'customer' => $this->normalizeCustomer($customer, $conversation->customer_email),
            'mailbox' => [
                'id' => optional($mailbox)->id,
                'name' => optional($mailbox)->name,
                'email' => optional($mailbox)->email,
                'emails' => $mailbox ? $mailbox->getEmails() : [],
            ],
            'assignee' => [
                'id' => $conversation->user_id,
            ],
            'threads_count' => $conversation->threads_count,
            'created_at' => $this->normalizeTimestamp($conversation->created_at),
            'updated_at' => $this->normalizeTimestamp($conversation->updated_at),
            'last_reply_at' => $this->normalizeTimestamp($conversation->last_reply_at),
            'closed_at' => $this->normalizeTimestamp($conversation->closed_at),
        ];
    }

    protected function normalizeThread(Thread $thread)
    {
        $bodyHtml = (string) ($thread->body ?: '');
        $bodyText = trim((string) $thread->getBodyAsText(['width' => 0]));
        $conversation = $thread->conversation;

        return [
            'id' => $thread->id,
            'conversation_id' => $thread->conversation_id,
            'customer_id' => $thread->customer_id,
            'user_id' => $thread->user_id,
            'type' => [
                'id' => $thread->type,
                'name' => Thread::$types[$thread->type] ?? null,
            ],
            'state' => [
                'id' => $thread->state,
                'name' => Thread::$states[$thread->state] ?? null,
            ],
            'status' => [
                'id' => $thread->status,
                'name' => Thread::$statuses[$thread->status] ?? null,
            ],
            'source' => [
                'type_id' => $thread->source_type,
                'type_name' => Thread::$source_types[$thread->source_type] ?? null,
                'via_id' => $thread->source_via,
                'via_name' => Thread::$persons[$thread->source_via] ?? null,
            ],
            'is_forward' => $thread->isForward(),
            'customer' => $this->normalizeCustomer($thread->customer ?: optional($conversation)->customer, optional($conversation)->customer_email),
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'body_preview' => \Helper::textPreview($bodyText ?: $bodyHtml, Conversation::PREVIEW_MAXLENGTH),
            'created_at' => $this->normalizeTimestamp($thread->created_at),
            'updated_at' => $this->normalizeTimestamp($thread->updated_at),
            'edited_at' => $this->normalizeTimestamp($thread->edited_at),
        ];
    }

    protected function normalizeLatestThread(Conversation $conversation)
    {
        $thread = $conversation->threads->first();

        if (!$thread) {
            return null;
        }

        return [
            'id' => $thread->id,
            'type' => Thread::$types[$thread->type] ?? null,
            'state' => Thread::$states[$thread->state] ?? null,
            'created_at' => $this->normalizeTimestamp($thread->created_at),
            'body_preview' => \Helper::textPreview(
                trim((string) $thread->getBodyAsText(['width' => 0])),
                Conversation::PREVIEW_MAXLENGTH
            ),
        ];
    }

    protected function normalizeCustomer($customer, $fallbackEmail = null)
    {
        if (!($customer instanceof Customer)) {
            return [
                'id' => null,
                'name' => '',
                'email' => $fallbackEmail,
                'emails' => $fallbackEmail ? [$fallbackEmail] : [],
            ];
        }

        $emails = $customer->relationLoaded('emails')
            ? $customer->emails->pluck('email')->filter()->values()->all()
            : array_filter([$customer->getMainEmail()]);

        $mainEmail = $customer->getMainEmail() ?: $fallbackEmail;

        if ($mainEmail && !in_array($mainEmail, $emails)) {
            array_unshift($emails, $mainEmail);
        }

        return [
            'id' => $customer->id,
            'name' => $customer->getFullName(true, false),
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $mainEmail,
            'emails' => array_values(array_unique(array_filter($emails))),
        ];
    }

    protected function normalizeChanges(array $changes)
    {
        foreach ($changes as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $changes[$key] = $this->normalizeTimestamp($value);
            }
        }

        return $changes;
    }

    protected function normalizeTimestamp($value)
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->toIso8601String();
        }

        return (string) $value;
    }
}

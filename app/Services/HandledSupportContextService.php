<?php

namespace App\Services;

use App\Conversation;
use GuzzleHttp\Client;

class HandledSupportContextService
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function lookupForConversation($conversation)
    {
        if (!($conversation instanceof Conversation) || !$conversation->id) {
            return null;
        }

        $url = $this->getContextUrl();
        $secret = trim((string) config('app.handled_support_sync_secret'));

        if (!$url || !$secret) {
            return null;
        }

        $customerEmail = trim((string) $conversation->customer_email);
        if (!$customerEmail && $conversation->customer) {
            $customerEmail = trim((string) $conversation->customer->getMainEmail());
        }

        try {
            $response = $this->client->get($url, \Helper::setGuzzleDefaultOptions([
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$secret,
                    'User-Agent' => 'handled-freescout-support-context/1.0',
                    'X-Handled-Support-Sync-Secret' => $secret,
                ],
                'query' => [
                    'conversation_id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'customer_email' => $customerEmail,
                ],
            ]));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $payload = json_decode((string) $response->getBody(), true);

            return is_array($payload) ? $payload : null;
        } catch (\Exception $e) {
            \Log::warning('Handled support context lookup failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function getContextUrl()
    {
        $syncUrl = trim((string) config('app.handled_support_sync_url'));

        if (!$syncUrl) {
            return null;
        }

        return preg_replace('#/support/freescout/sync/?$#', '/support/freescout/context', $syncUrl);
    }
}

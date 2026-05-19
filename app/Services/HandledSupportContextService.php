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
        $secret = $this->getSupportSecret();

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
                'headers' => $this->getSupportHeaders($secret, 'handled-freescout-support-context/1.0'),
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

    public function triggerPasswordReset(array $payload, array $context = [])
    {
        return $this->postSupportAction('/support/freescout/actions/password-reset', $payload, $context + [
            'action' => 'password-reset',
        ]);
    }

    public function updateAccountState(array $payload, array $context = [])
    {
        return $this->postSupportAction('/support/freescout/actions/account-state', $payload, $context + [
            'action' => 'account-state',
        ]);
    }

    protected function postSupportAction($path, array $payload, array $context = [])
    {
        $url = $this->getSupportUrl($path);
        $secret = $this->getSupportSecret();

        if (!$url || !$secret) {
            \Log::warning('Handled support action skipped: missing configuration', $context + [
                'url_configured' => (bool) $url,
                'secret_configured' => (bool) $secret,
            ]);

            return [
                'ok' => false,
                'message' => __('Handled support actions are unavailable right now.'),
            ];
        }

        try {
            $response = $this->client->post($url, \Helper::setGuzzleDefaultOptions([
                'http_errors' => false,
                'headers' => $this->getSupportHeaders($secret, 'handled-freescout-support-actions/1.0') + [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]));

            $responseBody = (string) $response->getBody();
            $decoded = json_decode($responseBody, true);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                \Log::warning('Handled support action request failed', $context + [
                    'status' => $response->getStatusCode(),
                    'response' => substr($responseBody, 0, 1000),
                ]);

                return [
                    'ok' => false,
                    'message' => is_array($decoded) && !empty($decoded['message']) ? $decoded['message'] : __('Handled support action failed.'),
                ];
            }

            if (!is_array($decoded)) {
                \Log::warning('Handled support action response was not valid JSON', $context + [
                    'status' => $response->getStatusCode(),
                    'response' => substr($responseBody, 0, 1000),
                ]);

                return [
                    'ok' => false,
                    'message' => __('Handled support action returned an invalid response.'),
                ];
            }

            return $decoded;
        } catch (\Exception $e) {
            \Log::warning('Handled support action request threw exception', $context + [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Handled support action failed.'),
            ];
        }
    }

    protected function getContextUrl()
    {
        return $this->getSupportUrl('/support/freescout/context');
    }

    protected function getSupportUrl($path)
    {
        $baseUrl = $this->getSupportBaseUrl();

        if (!$baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
    }

    protected function getSupportBaseUrl()
    {
        $syncUrl = trim((string) config('app.handled_support_sync_url'));

        if (!$syncUrl) {
            return null;
        }

        if (preg_match('#^(.*)/support/freescout/sync/?$#', $syncUrl, $matches)) {
            return rtrim($matches[1], '/');
        }

        return rtrim($syncUrl, '/');
    }

    protected function getSupportSecret()
    {
        return trim((string) config('app.handled_support_sync_secret'));
    }

    protected function getSupportHeaders($secret, $userAgent)
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$secret,
            'User-Agent' => $userAgent,
            'X-Handled-Support-Sync-Secret' => $secret,
        ];
    }
}

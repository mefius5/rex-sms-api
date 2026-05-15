<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

readonly class SmsApiPlProvider implements SmsProvider
{
    public function __construct(
        private string  $apiToken,
        private string  $baseUrl,
        private ?string $from = null,
    ) {}

    public function send(string $recipient, string $message): array
    {
        $payload = [
            'to' => $recipient,
            'message' => $message,
            'format' => 'json',
        ];

        if ($this->from) {
            $payload['from'] = $this->from;
        }

        $response = Http::withToken($this->apiToken)
            ->asForm()
            ->post("{$this->baseUrl}/sms.do", $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "SMSAPI request failed: HTTP {$response->status()}"
            );
        }

        $body = $response->json();

        if (isset($body['error'])) {
            throw new RuntimeException(
                "SMSAPI error {$body['error']}: {$body['message']}"
            );
        }

        $item = $body['list'][0];

        return [
            'external_id' => (string) $item['id'],
            'status' => $item['status'],
        ];
    }
}

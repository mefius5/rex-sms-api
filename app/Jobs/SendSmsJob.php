<?php

namespace App\Jobs;

use App\Contracts\SmsProvider;
use App\Models\SmsMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function __construct(
        public SmsMessage $smsMessage,
    ) {}

    public function handle(SmsProvider $provider): void
    {
        try {
            $result = $provider->send(
                $this->smsMessage->recipient,
                $this->smsMessage->message,
            );

            $this->smsMessage->update([
                'external_id' => $result['external_id'],
                'status' => strtolower($result['status']),
                'sent_at' => now(),
            ]);
        } catch (RuntimeException $e) {
            $this->smsMessage->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}

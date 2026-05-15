<?php

namespace App\Services\Sms;

use App\Jobs\SendSmsJob;
use App\Models\SmsMessage;

readonly class SmsService
{
    public function send(string $recipient, string $message): SmsMessage
    {
        $smsMessage = SmsMessage::create([
            'recipient' => $recipient,
            'message' => $message,
            'provider' => config('sms.default'),
            'status' => 'pending',
        ]);

        SendSmsJob::dispatch($smsMessage);

        return $smsMessage;
    }
}

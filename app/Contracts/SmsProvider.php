<?php

namespace App\Contracts;

interface SmsProvider
{
    /**
     * Send an SMS message.
     *
     * @return array{external_id: string, status: string}
     *
     * @throws \RuntimeException
     */
    public function send(string $recipient, string $message): array;
}

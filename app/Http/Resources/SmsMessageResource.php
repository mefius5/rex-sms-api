<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient' => $this->recipient,
            'message' => $this->message,
            'provider' => $this->provider,
            'status' => $this->status,
            'external_id' => $this->external_id,
            'error_message' => $this->when($this->status === 'failed', $this->error_message),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

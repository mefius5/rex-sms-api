<?php

namespace Database\Factories;

use App\Models\SmsMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SmsMessage> */
class SmsMessageFactory extends Factory
{
    protected $model = SmsMessage::class;

    public function definition(): array
    {
        return [
            'recipient' => $this->faker->e164PhoneNumber(),
            'message' => $this->faker->sentence(),
            'provider' => 'smsapi',
            'status' => 'sent',
            'external_id' => $this->faker->uuid(),
            'sent_at' => now(),
        ];
    }
}

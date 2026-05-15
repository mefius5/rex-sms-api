<?php

namespace Tests\Feature;

use App\Contracts\SmsProvider;
use App\Jobs\SendSmsJob;
use App\Models\SmsMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SmsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/sms', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recipient', 'message']);
    }

    #[Test]
    public function store_validates_recipient_format(): void
    {
        $response = $this->postJson('/api/sms', [
            'recipient' => 'not-a-number',
            'message' => 'Hello',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recipient']);
    }

    #[Test]
    public function store_validates_message_max_length(): void
    {
        $response = $this->postJson('/api/sms', [
            'recipient' => '+48123456789',
            'message' => str_repeat('a', 919),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    #[Test]
    public function store_creates_pending_sms_and_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/sms', [
            'recipient' => '+48123456789',
            'message' => 'Test message',
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('data.recipient', '+48123456789')
            ->assertJsonPath('data.message', 'Test message')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('sms_messages', [
            'recipient' => '+48123456789',
            'message' => 'Test message',
            'status' => 'pending',
        ]);

        Queue::assertPushed(SendSmsJob::class, function (SendSmsJob $job) {
            return $job->smsMessage->recipient === '+48123456789';
        });
    }

    #[Test]
    public function store_dispatched_job_sends_sms_via_provider(): void
    {
        $provider = $this->createMock(SmsProvider::class);
        $provider->method('send')
            ->willReturn(['external_id' => 'ext-123', 'status' => 'SENT']);

        $this->app->instance(SmsProvider::class, $provider);

        $response = $this->postJson('/api/sms', [
            'recipient' => '+48123456789',
            'message' => 'Test message',
        ]);

        $response->assertStatus(202);

        $smsMessage = SmsMessage::first();
        $this->assertSame('sent', $smsMessage->status);
        $this->assertSame('ext-123', $smsMessage->external_id);
        $this->assertNotNull($smsMessage->sent_at);
    }

    #[Test]
    public function store_dispatched_job_marks_sms_as_failed_on_provider_error(): void
    {
        $provider = $this->createMock(SmsProvider::class);
        $provider->method('send')
            ->willThrowException(new RuntimeException('API down'));

        $this->app->instance(SmsProvider::class, $provider);

        $response = $this->postJson('/api/sms', [
            'recipient' => '+48123456789',
            'message' => 'Test message',
        ]);

        $response->assertStatus(202);

        $smsMessage = SmsMessage::first();
        $this->assertSame('failed', $smsMessage->status);
        $this->assertSame('API down', $smsMessage->error_message);
    }

    #[Test]
    public function index_returns_paginated_sms_messages(): void
    {
        SmsMessage::factory()->count(3)->create();

        $response = $this->getJson('/api/sms');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'recipient', 'message', 'provider', 'status', 'external_id', 'sent_at', 'created_at'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    #[Test]
    public function index_returns_messages_in_descending_order(): void
    {
        $older = SmsMessage::factory()->create(['created_at' => now()->subHour()]);
        $newer = SmsMessage::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/sms');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    #[Test]
    public function index_paginates_at_20_per_page(): void
    {
        SmsMessage::factory()->count(25)->create();

        $response = $this->getJson('/api/sms');

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25);
    }

    #[Test]
    public function index_returns_empty_list_when_no_messages(): void
    {
        $response = $this->getJson('/api/sms');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}

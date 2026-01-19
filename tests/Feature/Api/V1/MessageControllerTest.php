<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\Message;
use App\Models\MessageSend;
use App\Enums\MessageSendStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_sent_messages()
    {
        $customer = Customer::factory()->create();
        $message = Message::factory()->create();

        MessageSend::factory()->count(5)->create([
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'status' => MessageSendStatus::SENT,
            'sent_at' => now()
        ]);

        $response = $this->getJson('/api/v1/messages/sent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page']
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_pagination()
    {
        $customer = Customer::factory()->create();
        $message = Message::factory()->create();

        MessageSend::factory()->count(60)->create([
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'status' => MessageSendStatus::SENT,
            'sent_at' => now()
        ]);

        $response = $this->getJson('/api/v1/messages/sent?page=2&per_page=20');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.current_page'));
        $this->assertCount(20, $response->json('data'));
    }

    public function test_only_sent_status()
    {
        $customer = Customer::factory()->create();
        $message = Message::factory()->create();

        MessageSend::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'status' => MessageSendStatus::SENT,
            'sent_at' => now()
        ]);

        MessageSend::factory()->count(5)->create([
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'status' => MessageSendStatus::PENDING
        ]);

        $response = $this->getJson('/api/v1/messages/sent');

        $this->assertCount(3, $response->json('data'));
    }
}

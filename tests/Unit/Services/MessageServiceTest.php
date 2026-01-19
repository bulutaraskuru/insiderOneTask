<?php

namespace Tests\Unit\Services;

use App\Jobs\SendMessageJob;
use App\Models\MessageSend;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use App\Services\MessageService;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $messageSendRepo = $this->createMock(MessageSendRepositoryInterface::class);
        $messageRepo = $this->createMock(MessageRepositoryInterface::class);
        $webhookService = $this->createMock(WebhookService::class);

        $this->service = new MessageService($messageSendRepo, $messageRepo, $webhookService);
        $this->messageSendRepo = $messageSendRepo;
        $this->messageRepo = $messageRepo;
    }

    public function test_dispatches_jobs_for_pending_messages()
    {
        Queue::fake();

        $msg = MessageSend::factory()->make(['id' => 1, 'message_content' => 'test']);
        $this->messageSendRepo->expects($this->once())
            ->method('chunkPending')
            ->willReturnCallback(function ($size, $callback) use ($msg) {
                $callback(collect([$msg]));
            });

        $count = $this->service->sendMessages(5);

        $this->assertEquals(1, $count);
        Queue::assertPushed(SendMessageJob::class);
    }

    public function test_validates_long_messages()
    {
        Queue::fake();

        $longMsg = MessageSend::factory()->make([
            'id' => 1,
            'message_content' => str_repeat('a', 161)
        ]);

        $this->messageSendRepo->expects($this->once())
            ->method('chunkPending')
            ->willReturnCallback(function ($size, $callback) use ($longMsg) {
                $callback(collect([$longMsg]));
            });
        $this->messageSendRepo->expects($this->once())->method('setFailed');

        $count = $this->service->sendMessages(5);

        $this->assertEquals(0, $count);
    }

    public function test_cache_works()
    {
        Cache::flush();
        Cache::put('sent:1:50', 'test_data', 300);

        $result = $this->service->getSentMessages(50);

        $this->assertEquals('test_data', $result);
    }
}

<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendMessageJob;
use App\Models\MessageSend;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use App\Services\MessageService;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_message()
    {
        Cache::flush();

        $msg = MessageSend::factory()->make([
            'id' => 1,
            'phone_number' => '905551234567',
            'message_content' => 'Test',
            'message_id' => 1
        ]);

        $webhook = $this->createMock(WebhookService::class);
        $webhook->method('sendDto')->willReturn('msg_12345');

        $sendRepo = $this->createMock(MessageSendRepositoryInterface::class);
        $sendRepo->expects($this->once())->method('setSent');

        $msgRepo = $this->createMock(MessageRepositoryInterface::class);
        $msgRepo->expects($this->once())->method('updateSentCount');

        $msgService = $this->createMock(MessageService::class);
        $msgService->expects($this->once())->method('clearSentCache');

        $job = new SendMessageJob($msg);
        $job->handle($webhook, $sendRepo, $msgRepo, $msgService);

        $this->assertEquals('msg_12345', Cache::get('job:message_send:1'));
    }

    public function test_prevents_duplicate()
    {
        Cache::flush();
        Cache::put('job:message_send:1', 'existing', 300);

        $msg = MessageSend::factory()->make(['id' => 1]);

        $webhook = $this->createMock(WebhookService::class);
        $webhook->expects($this->never())->method('sendDto');

        $job = new SendMessageJob($msg);
        $job->handle(
            $webhook,
            $this->createMock(MessageSendRepositoryInterface::class),
            $this->createMock(MessageRepositoryInterface::class),
            $this->createMock(MessageService::class)
        );
    }

    public function test_retry_config()
    {
        $msg = MessageSend::factory()->make();
        $job = new SendMessageJob($msg);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 180], $job->backoff);
    }
}

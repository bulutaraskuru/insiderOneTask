<?php

namespace App\Jobs;

use App\DTOs\SendWebhookMessageDto;
use App\Models\MessageSend;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\WebhookService;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180];

    public function __construct(
        public MessageSend $messageSend
    ) {}
    public function handle(
        WebhookService $webhookService,
        MessageSendRepositoryInterface $messageSendRepository,
        MessageRepositoryInterface $messageRepository,
        MessageService $messageService
    ): void {

        // tekrar gönderim kontrolü
        $cacheKey = "job:message_send:{$this->messageSend->id}";

        if (Cache::has($cacheKey)) {
            Log::info('Mesaj zaten gönderilmiş.', [
                'id' => $this->messageSend->id
            ]);
            return;
        }

        Cache::put($cacheKey, true, 300); // 5 dk lock

        try {
            // webhook burada
            $dto = new SendWebhookMessageDto(
                $this->messageSend->phone_number,
                $this->messageSend->message_content,
                $this->messageSend->id
            );

            $externalMessageId = $webhookService->sendDto($dto);

            if ($externalMessageId) {
                $messageSendRepository->setSent($this->messageSend->id, $externalMessageId);
                $messageRepository->updateSentCount($this->messageSend->message_id);
                $messageService->clearSentCache();

                Cache::put($cacheKey, $externalMessageId, 172800); // 2 gün

                Log::info('Mesaj başarıyla gönderildi', [
                    'id' => $this->messageSend->id,
                    'webhook_id' => $externalMessageId
                ]);
            } else {
                Cache::forget($cacheKey);
                $messageSendRepository->setFailed($this->messageSend->id, 'Webhook yanıt vermedi');
                throw new \Exception('Webhook messageId döndürmedi');
            }
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            Log::error('Job başarısız', [
                'id' => $this->messageSend->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

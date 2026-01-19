<?php

namespace App\Services;

use App\Jobs\SendMessageJob;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MessageService
{
    private const MAX_MESSAGE_LENGTH = 160;
    private const MESSAGE_DELAY = 5;
    private const CACHE_TTL = 300; // 5 dakika
    private const CHUNK_SIZE = 200;

    public function __construct(
        private MessageSendRepositoryInterface $messageSendRepo,
        private MessageRepositoryInterface $messageRepo,
        private WebhookService $webhookService,
    ) {}

    public function sendMessages(int $limit = 2)
    {
        Log::info('sendMessages çağrıldı', ['limit' => $limit]);

        $sentCount = 0;
        $remaining = max(0, $limit);

        if ($remaining === 0) {
            Log::warning('Limit 0, işlem yok');
            return 0;
        }

        $this->messageSendRepo->chunkPending(self::CHUNK_SIZE, function ($pendingMessages) use (&$sentCount, &$remaining) {
            foreach ($pendingMessages as $messageSend) {
                if ($remaining <= 0) {
                    return false;
                }

                // Karakter kontrolü
                if (!$this->validateMessageContent($messageSend->message_content)) {
                    Log::warning('Mesaj içeriği çok uzun', ['message_send_id' => $messageSend->id]);
                    $this->messageSendRepo->setFailed($messageSend->id, 'Karakter sınırı aşıldı');
                    continue;
                }

                // 2 mesaj / 5 saniye kuralı
                $delaySeconds = intdiv($sentCount, 2) * self::MESSAGE_DELAY;

                // Job'ı kuyruğa ekle
                SendMessageJob::dispatch($messageSend)
                    ->delay(now()->addSeconds($delaySeconds));

                $sentCount++;
                $remaining--;

                Log::info('Job kuyruğa eklendi', ['message_send_id' => $messageSend->id]);
            }

            return $remaining > 0;
        });

        if ($sentCount === 0) {
            Log::warning('Pending mesaj yok');
        }

        Log::info('Toplam gönderilen mesaj', ['count' => $sentCount]);

        return $sentCount;
    }


    public function getSentMessages(int $perPage = 50)
    {
        $page = request()->get('page', 1);
        $key = "sent:{$page}:{$perPage}";

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $messages = $this->messageSendRepo->getSentMessages($perPage);
        Cache::put($key, $messages, self::CACHE_TTL);

        return $messages;
    }

    public function clearSentCache()
    {
        $pattern = config('cache.prefix') . ':sent:*';
        $keys = Cache::getRedis()->keys($pattern);

        foreach ($keys as $key) {
            $shortKey = str_replace(config('cache.prefix') . ':', '', $key);
            Cache::forget($shortKey);
        }
    }

    private function validateMessageContent(string $content): bool
    {
        return mb_strlen($content) <= self::MAX_MESSAGE_LENGTH;
    }
}

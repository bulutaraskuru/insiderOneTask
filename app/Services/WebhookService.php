<?php

namespace App\Services;

use App\DTOs\SendWebhookMessageDto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    private const CACHE_TTL = 172800; // 2 gün
    private const CACHE_PREFIX = 'webhook:message_send:';
    private string $webhookUrl;
    private string $authKey;

    public function __construct()
    {
        $this->webhookUrl = config('services.webhook.url', '');
        $this->authKey = config('services.webhook.auth_key', '');
    }

    public function send(string $phoneNumber, string $content, ?int $messageSendId = null): ?string
    {

        if (empty($this->webhookUrl)) {
            Log::error('Webhook URL boş');
            return null;
        }

        // Basit rate limit (dakikada 50 istek)
        $rateLimitKey = 'webhook_limit:' . now()->format('YmdHi');
        $count = Cache::get($rateLimitKey, 0);

        if ($count >= 50) {
            Log::warning('Rate limit aşıldı', ['phone' => $phoneNumber]);
            sleep(1);
            if (Cache::get($rateLimitKey, 0) >= 50) {
                return null;
            }
        }

        $newCount = Cache::increment($rateLimitKey);
        if ($newCount === 1) {
            Cache::put($rateLimitKey, $newCount, 60);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-ins-auth-key' => $this->authKey,
                ])
                ->post($this->webhookUrl, [
                    'to' => $phoneNumber,
                    'content' => $content,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messageId'] ?? null;

                Log::info('Webhook Success', [
                    'status' => $response->status(),
                    'phone' => $phoneNumber,
                    'messageId' => $messageId,
                ]);

                // Redis cache'le
                if ($messageId && $messageSendId) {
                    $this->cacheMessageInfo($messageSendId, $messageId, $phoneNumber);
                }

                return $messageId;
            }

            Log::error('Webhook Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'phone' => $phoneNumber,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Webhook exception', [
                'message' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);

            return null;
        }
    }

    public function sendDto(SendWebhookMessageDto $dto): ?string
    {
        return $this->send($dto->phoneNumber, $dto->content, $dto->messageSendId);
    }

    /**
     * Mesaj bilgilerini Redis'e cache'ler (2 gün TTL)
     */
    private function cacheMessageInfo(int $messageSendId, string $messageId, string $phoneNumber): void
    {
        $cacheKey = self::CACHE_PREFIX . $messageSendId;

        $data = [
            'message_send_id' => $messageSendId,
            'webhook_message_id' => $messageId,
            'phone_number' => $phoneNumber,
            'sent_at' => now()->toIso8601String(),
            'cached_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $data, self::CACHE_TTL);

        Log::info('Message info cached', [
            'cache_key' => $cacheKey,
            'ttl_days' => self::CACHE_TTL / 86400,
        ]);
    }

    /**
     * Cache'den mesaj bilgilerini okur
     */
    public function getCachedMessageInfo(int $messageSendId): ?array
    {
        $cacheKey = self::CACHE_PREFIX . $messageSendId;

        $data = Cache::get($cacheKey);

        if ($data) {
            Log::info('Cache hit', ['cache_key' => $cacheKey]);
        } else {
            Log::info('Cache miss', ['cache_key' => $cacheKey]);
        }

        return $data;
    }

    /**
     * Cache'den mesaj bilgilerini siler
     */
    public function removeCachedMessageInfo(int $messageSendId): bool
    {
        $cacheKey = self::CACHE_PREFIX . $messageSendId;
        return Cache::forget($cacheKey);
    }
}

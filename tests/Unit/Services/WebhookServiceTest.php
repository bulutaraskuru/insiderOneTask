<?php

namespace Tests\Unit\Services;

use App\Services\WebhookService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.webhook.url' => 'https://test.webhook.site/test']);
        config(['services.webhook.auth_key' => 'test-key']);

        $this->service = new WebhookService();
    }

    public function test_sends_message_and_returns_id()
    {
        Http::fake(['*' => Http::response(['messageId' => 'msg_123'], 200)]);

        $result = $this->service->send('905551234567', 'Test', 1);

        $this->assertEquals('msg_123', $result);
    }

    public function test_returns_null_on_error()
    {
        Http::fake(['*' => Http::response([], 500)]);

        $result = $this->service->send('905551234567', 'Test', 1);

        $this->assertNull($result);
    }

    public function test_rate_limit()
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['messageId' => 'test'], 200)]);

        for ($i = 0; $i < 50; $i++) {
            $this->service->send('905551234567', 'Test', $i);
        }

        $key = "webhook_limit:" . now()->format('YmdHi');
        $this->assertGreaterThan(45, Cache::get($key));
    }
}

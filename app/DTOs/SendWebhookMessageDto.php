<?php

namespace App\DTOs;

class SendWebhookMessageDto
{
    public function __construct(
        public string $phoneNumber,
        public string $content,
        public ?int $messageSendId = null
    ) {}
}

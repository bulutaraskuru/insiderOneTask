<?php

namespace App\Repositories\Contracts;

use App\Models\Message;

interface MessageRepositoryInterface
{
    public function find(int $id): ?Message;

    public function updateSentCount(int $messageId): void;
}

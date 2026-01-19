<?php

namespace App\Repositories;

use App\Enums\MessageSendStatus;
use App\Models\MessageSend;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MessageSendRepository implements MessageSendRepositoryInterface
{
    public function getPending(int $limit): Collection
    {
        return MessageSend::with(['customer', 'message'])
            ->where('status', MessageSendStatus::PENDING)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    public function chunkPending(int $chunkSize, callable $callback): void
    {
        MessageSend::with(['customer', 'message'])
            ->where('status', MessageSendStatus::PENDING)
            ->orderBy('id')
            ->chunkById($chunkSize, $callback);
    }

    public function setSent(int $id, string $externalMessageId): void
    {
        MessageSend::with(['customer', 'message'])
            ->where('id', $id)
            ->update([
                'status' => MessageSendStatus::SENT,
                'webhook_message_id' => $externalMessageId,
                'sent_at' => now()
            ]);
    }

    public function setFailed(int $id, string $error = null): void
    {
        MessageSend::with(['customer', 'message'])
            ->where('id', $id)
            ->update([
                'status' => MessageSendStatus::FAILED
            ]);
    }

    public function getSentMessages(int $perPage = 50): LengthAwarePaginator
    {
        return MessageSend::with(['customer', 'message'])
            ->where('status', MessageSendStatus::SENT)
            ->orderByDesc('sent_at')
            ->paginate($perPage);
    }
}

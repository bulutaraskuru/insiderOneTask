<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MessageSendRepositoryInterface
{
    public function getPending(int $limit): Collection;

    public function chunkPending(int $chunkSize, callable $callback): void;

    public function setSent(int $id, string $externalMessageId): void;

    public function setFailed(int $id, string $error = null): void;

    public function getSentMessages(int $perPage = 50): LengthAwarePaginator;
}

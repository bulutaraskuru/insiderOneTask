<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Support\Collection;

interface CustomerRepositoryInterface
{
    public function getAll(): Collection;

    public function getFind(int $id): ?Customer;

    public function getActive(): Collection;
}

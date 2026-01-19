<?php


namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Support\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{

    public function getAll(): Collection
    {
        return Customer::all();
    }
    public function getFind(int $id): ?Customer
    {
        return Customer::find($id);
    }
    public function getActive(): Collection
    {
        return Customer::where('status', true)->get();
    }
}

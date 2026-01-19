<?php

namespace Database\Seeders;


use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 95 aktif müşteri
        Customer::factory()
            ->count(95)
            ->active()
            ->create();

        // 5 pasif müşteri
        Customer::factory()
            ->count(12)
            ->inactive()
            ->create();

        $this->command->info('107 müşteri oluşturuldu (95 aktif, 12 pasif)');
    }
}

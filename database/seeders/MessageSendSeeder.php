<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Message;
use App\Models\MessageSend;
use App\Enums\MessageSendStatus;
use Illuminate\Database\Seeder;

class MessageSendSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::where('is_active', true)->get();
        $messages = Message::all();

        if ($customers->isEmpty()) {
            $this->command->error('Hiç aktif müşteri yok! ');
            return;
        }

        if ($messages->isEmpty()) {
            $this->command->error('Hiç mesaj yok!');
            return;
        }

        $this->command->info('28 PENDING');

        for ($i = 0; $i < 28; $i++) {
            $customer = $customers->random();
            $message = $messages->random();

            MessageSend::create([
                'customer_id' => $customer->id,
                'message_id' => $message->id,
                'phone_number' => $customer->phone_number,
                'message_content' => $message->content,
                'status' => MessageSendStatus::PENDING,
                'webhook_message_id' => null,
                'sent_at' => null,
            ]);
        }

        $this->command->info('Test için 5 SENT');

        for ($i = 0; $i < 5; $i++) {
            MessageSend::create([
                'customer_id' => $customers->random()->id,
                'message_id' => $messages->random()->id,
                'phone_number' => $customers->random()->phone_number,
                'message_content' => $messages->random()->content,
                'status' => MessageSendStatus::SENT,
                'webhook_message_id' => 'msg_' . uniqid(),
                'sent_at' => now()->subHours(rand(1, 24)),
            ]);
        }

        $this->command->info('Test için 2 FAILED');

        for ($i = 0; $i < 2; $i++) {
            MessageSend::create([
                'customer_id' => $customers->random()->id,
                'message_id' => $messages->random()->id,
                'phone_number' => $customers->random()->phone_number,
                'message_content' => $messages->random()->content,
                'status' => MessageSendStatus::FAILED,
                'webhook_message_id' => null,
                'sent_at' => null,
            ]);
        }

        $pendingCount = MessageSend::where('status', MessageSendStatus::PENDING)->count();
        $sentCount = MessageSend::where('status', MessageSendStatus::SENT)->count();
        $failedCount = MessageSend::where('status', MessageSendStatus::FAILED)->count();

        $this->command->info("MessageSend kayıtları oluşturuldu:");
        $this->command->info("   - PENDING: {$pendingCount}");
        $this->command->info("   - SENT: {$sentCount}");
        $this->command->info("   - FAILED:  {$failedCount}");
    }
}

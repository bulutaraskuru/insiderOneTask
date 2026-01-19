<?php

namespace Database\Factories;

use App\Enums\MessageSendStatus;
use App\Models\Customer;
use App\Models\Message;
use App\Models\MessageSend;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageSendFactory extends Factory
{
    protected $model = MessageSend::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'message_id' => Message::factory(),
            'phone_number' => '+9055' . fake()->numerify('########'),
            'message_content' => fake()->text(150),
            'status' => MessageSendStatus::PENDING,
            'webhook_message_id' => null,
            'sent_at' => null,
        ];
    }
}

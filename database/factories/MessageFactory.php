<?php

namespace Database\Factories;

use App\Enums\MessageStatus;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->text(150),
            'status' => MessageStatus::DRAFT,
            'sent_count' => 0,
        ];
    }
}

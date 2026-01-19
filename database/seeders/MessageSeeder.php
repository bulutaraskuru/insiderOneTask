<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'title' => 'Hoşgeldin Mesajı',
                'content' => 'Merhaba! Sistemimize hoş geldiniz. İyi günler dileriz.',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
            [
                'title' => 'Kampanya Duyurusu',
                'content' => 'Özel indirim! %50 fırsat sadece bugün geçerli.',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
            [
                'title' => 'Hatırlatma',
                'content' => 'Randevunuz yarın saat 14:00\'te. Lütfen zamanında gelin.',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
            [
                'title' => 'Test Mesajı',
                'content' => 'Bu bir test mesajıdır. Insider - Project',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
            [
                'title' => 'Uzun Mesaj Test 1',
                'content' => 'Bu mesaj 160 karakterden daha uzun bir mesajdır ve karakter sınırını aşmaktadır. Sistemimiz bu mesajı göndermeyecek ve failed olarak işaretleyecektir. Bu bir test mesajıdır.',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
            [
                'title' => 'Uzun Mesaj Test 2',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam quis nostrud exercitation ullamco laboris.',
                'status' => \App\Enums\MessageStatus::DRAFT,
                'sent_count' => 0,
            ],
        ];

        foreach ($messages as $message) {
            Message::create($message);
        }

        $this->command->info('6 mesaj oluşturuldu (2 tanesi 160 karakter üzeri)');
    }
}

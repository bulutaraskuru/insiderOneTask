<?php

namespace App\Models;

use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    // fillable alanları tanımlaması
    protected $fillable = [
        "title",
        "content",
        "status",
        "sent_count"
    ];

    protected $casts = [
        'sent_count' => 'integer',
        'status' => MessageStatus::class
    ];
}

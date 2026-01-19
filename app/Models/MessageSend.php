<?php

namespace App\Models;

use App\Enums\MessageSendStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSend extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'message_id',
        'customer_id',
        'phone_number',
        'message_content',
        'status',
        'webhook_message_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'status' => MessageSendStatus::class,

    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}

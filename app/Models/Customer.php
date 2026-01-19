<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    // müşteri modeli fillable alanları soft delete tanımlaması eloquent gerekli yapılandırma

    use SoftDeletes, HasFactory;

    protected $fillable = [
        "name",
        'phone_number',
        'email',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** api responseları gizlemek için */
    protected $hidden = [
        'deleted_at'
    ];

    public function messageSends()
    {
        return $this->hasMany(MessageSend::class);
    }
}

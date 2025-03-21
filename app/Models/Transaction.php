<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string'
    ];

    /**
     * Get the sender (can be either a user or company).
     */
    public function sender()
    {
        return $this->morphTo('sender', null, 'sender_id');
    }

    /**
     * Get the receiver (can be either a user or company).
     */
    public function receiver()
    {
        return $this->morphTo('receiver', null, 'receiver_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'request_type',
        'request_id',
        'transaction_origin',
        'transaction_destination',
        'amount',
        'currency',
        'delivery_confirmation_status',
        'transaction_stage',
        'confirmation_status',
        'transaction_complete_status',
        'transaction_notes',
    ];

    protected $casts = [
        'transaction_notes' => 'array',
    ];

    // 🔁 The user who initiated the transaction
    public function origin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transaction_origin');
    }

    // 🔁 The moderator or peer who receives the transaction
    public function destination(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transaction_destination');
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class, 'request_id');
    }

    public function originUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transaction_origin');
    }

    public function destinationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transaction_destination');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'initiator',
        'moderator_account_id',
        'request_status',
        'notes',
    ];

    protected $casts = [
        'notes' => 'array',
    ];

    // 🔁 The user who initiated the withdrawal
    public function initiatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator');
    }

    // 🔁 The moderator receiving the request
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_account_id');
    }


    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'request_id')
            ->where('request_type', 'withdrawal');
    }
}

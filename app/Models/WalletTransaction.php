<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public $timestamps = false; // в таблице нет updated_at/created_at автоматических

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'created_at'
    ];

    // Кому принадлежит эта транзакция
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

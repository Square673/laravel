<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'quest_id',
        'date',
        'time',
        'players_count',
        'total_price',
        'status',
        'phone', // Добавлено поле для телефона
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}




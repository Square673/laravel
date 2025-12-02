<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'duration'
    ];

    // Все бронирования этого квеста
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

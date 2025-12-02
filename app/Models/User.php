<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
    'name',
    'phone',
    'email',
    'password',
    'role',
    'balance',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Бронирования
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Финансовые операции
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}

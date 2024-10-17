<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at', // Дополнительно скрываем для безопасности
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date', // birthday кастим как дату
    ];
}

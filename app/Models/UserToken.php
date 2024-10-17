<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    // Таблица в базе данных, с которой связана модель
    protected $table = 'user_tokens';

    // Разрешенные для массового заполнения поля
    protected $fillable = [
        'user_id',
        'token',
    ];

    // Связь с моделью User (Каждый токен принадлежит одному пользователю)
    public function user()
    {
        return $this->belongsTo(User::class); 
    }
    // jwt-auth secret [tGpxzaJmEOJMgkaQvfEzOC3tjH3Wu4A8guchQSpzRS1N2JsZmviytHRoGd4CEk4y] set successfully
}
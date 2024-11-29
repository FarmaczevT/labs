<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\LogsChanges;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, LogsChanges;

    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
        'photo_id',
        'tfa_token',
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

    // Реализация методов интерфейса JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Возвращает идентификатор пользователя (например, user_id)
    }
    // Реализация метода getJWTCustomClaims для добавления кастомных данных в токен
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasPermission(string $permissionName) : bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('code', $permissionName);
        })->exists();
    }
}

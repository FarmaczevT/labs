<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'code',
        'description',
        'created_by'
    ];

    // Определим отношение к таблице users
    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'user_roles');
    // }
}

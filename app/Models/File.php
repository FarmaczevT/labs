<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filename',
        'description',
        'format',
        'size',
        'path',
    ];

    /**
     * Возвращает URL файла для доступа.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
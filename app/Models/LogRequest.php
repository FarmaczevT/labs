<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRequest extends Model
{
    use HasFactory;

    protected $table = 'logs_requests';

    protected $fillable = [
        'url',
        'http_method',
        'controller',
        'controller_method',
        'request_body',
        'request_headers',
        'user_id',
        'user_ip',
        'user_agent',
        'response_status',
        'response_body',
        'response_headers',
    ];
    // Автоматически преобразует данные при обращении
    protected $casts = [
        'request_body' => 'array',
        'request_headers' => 'array',
        'response_body' => 'array',
        'response_headers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code', 
        'expires_at', 
        'request_count', 
        'last_requested_at'
    ];

    protected $dates = ['expires_at', 'last_requested_at'];

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    public function setCode()
    {
        $this->code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->expires_at = Carbon::now()->addSeconds((int) env('TFA_CODE_EXPIRATION'));
        $this->last_requested_at = null;
        $this->save();
    }
}
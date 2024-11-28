<?php

namespace App\Services;

use App\Mail\TwoFactorCode;
use App\Models\TwoFactorAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthService
{
    public function generateCode($user)
    {
        $tfa = $user->twoFactorAuth ?: new TwoFactorAuth(['user_id' => $user->id]);

        // Значения из env с дефолтами
        $maxRequests = (int) env('TFA_MAX_REQUESTS', 3);
        $delaySeconds = (int) env('TFA_DELAY_SECONDS', 30);

        // Логика сброса только при достижении лимита запросов
        if ($tfa->request_count >= $maxRequests) {
            $diffInSeconds = round(abs(Carbon::now()->diffInSeconds($tfa->last_requested_at ?: Carbon::now())));

            // Если прошло достаточно времени, сбрасываем счетчик
            if ($diffInSeconds >= $delaySeconds) {
                $tfa->request_count = 0;
            } else {
                return [
                    'success' => false,
                    'diffInSeconds' => $diffInSeconds,
                    'delaySeconds' => $delaySeconds,
                ]; // Блокируем запрос до завершения задержки
            }
        }

        // Генерация нового кода
        $tfa->setCode();
        $tfa->last_requested_at = Carbon::now();
        $tfa->request_count++;
        $tfa->save();

        // Отправка email
        Mail::to($user->email)->send(new TwoFactorCode($tfa->code));

        return [
            'success' => true,
            'code' => $tfa->code,
        ];
    }


    public function verifyCode($user, $code)
    {
        $tfa = $user->twoFactorAuth;
        if (!$tfa || $tfa->code === null || $tfa->isExpired() || $tfa->code != $code) {
            return false;
        }
        
        $tfa->request_count = 0;
        // Очищаем код в записи, делая его недействительным
        $tfa->code = null;
        $tfa->save();

        return true;
    }
}
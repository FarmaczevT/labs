<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\LoginResource;
use Illuminate\Support\Facades\Cache;

class TwoFactorAuthController extends Controller
{
    protected $service;

    public function __construct(TwoFactorAuthService $service)
    {
        $this->service = $service;
    }

    // Проверка кода 2fa
    public function verifyCode(Request $request)
    {
        try {
            $request->validate(['code' => 'required|digits:6']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        
        $user = User::where('tfa_token', $request->tfa_token)->first();

        // Получение сохранённых данных
        $cachedData = Cache::get('2fa_' . $user->id);

        if (!$cachedData) {
            return response()->json(['error' => 'Код истёк или не был запрошен'], 404);
        }
    
        // Проверка User-Agent
        $currentUserAgent = $request->header('User-Agent');
        if ($cachedData['user_agent'] !== $currentUserAgent) {
            return response()->json(['error' => 'Запрос выполнен с другого устройства'], 403);
        }
    
        // Проверка IP-адреса
        $currentIp = $request->ip();
        if ($cachedData['ip'] !== $currentIp) {
            return response()->json(['error' => 'Запрос выполнен с другого IP-адреса'], 403);
        }

        if (!$this->service->verifyCode($user, $request->code)) {
            return response()->json(['message' => 'Код недействителен'], 400);
        }

        // Успешное подтверждение
        Cache::forget('2fa_code_' . $user->id); // Удаляем код из кэша

        // Проверка количества активных токенов
        $maxTokens = (int) env('MAX_ACTIVE_TOKENS', 4); // Получаем значение из переменной окружения или по умолчанию 5

        // Создание JWT токена
        $token = JWTAuth::fromUser($user);

        // Создание одноразового токена обновления
        $refreshToken = JWTAuth::fromUser(
            $user, 
            ['exp' => now()->addMinutes((int) env('JWT_REFRESH_TTL', 20160))->timestamp]
        );

        $user->tfa_token = null;
        $user->save();

        return response()->json([
            'status' => 'Successful authorization',
            'token' => $token,
            'refresh_token' => $refreshToken,
            'max_tokens' => $maxTokens,
            'user' => new LoginResource($user),
        ], 200);
    }

    // Включение/отключение 2fa для пользователя
    public function toggle2FA(Request $request)
    {
        $request->validate(['password' => 'required']);
        $user = $request->user();

        // Проверка пароля пользователя
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверный пароль'], 403);
        }

        // Проверяем, включён ли 2FA для пользователя
        $twoFactor = $user->twoFactorAuth;

        if ($twoFactor) {
            // Если 2FA включен, удаляем запись из таблицы two_factor_auths
            $twoFactor->delete();
            $message = 'Двухфакторная аутентификация отключена';
        } else {
            // Если 2FA отключен, добавляем запись в таблицу two_factor_auths
            $user->twoFactorAuth()->create([
                'code' => null, // Здесь может быть поле для кода, если оно нужно
                'expires_at' => null, // Или установите значения по умолчанию, если требуется
            ]);
            $message = 'Двухфакторная аутентификация включена';
        }

        return response()->json(['message' => $message]);
    }

    // Запрос нового кода 2fa
    public function requestingNewCode(Request $request)
    {
        $user = User::where('tfa_token', $request->tfa_token)->first();

        $result = $this->service->generateCode($user);

            if (!$result['success']) {
                $remainingTime = $result['delaySeconds'] - $result['diffInSeconds'];
                return response()->json(['message' => 'Подождите ' . $remainingTime . ' секунд перед следующим запросом'], 429);
            }

            // Отправка кода пользователю
            return response()->json(['message' => 'На ваш email отправлен код для подтверждения двухфакторной аутентификации']);
    }

}
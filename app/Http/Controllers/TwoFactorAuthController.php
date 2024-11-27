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
        
        $user = User::where('id', $request->id)->first();

        if (!$this->service->verifyCode($user, $request->code)) {
            return response()->json(['message' => 'Код недействителен'], 400);
        }

        // Проверка количества активных токенов
        $maxTokens = (int) env('MAX_ACTIVE_TOKENS', 4); // Получаем значение из переменной окружения или по умолчанию 5

        // Создание JWT токена
        $token = JWTAuth::fromUser($user);

        // Создание одноразового токена обновления
        $refreshToken = JWTAuth::fromUser(
            $user, 
            ['exp' => now()->addMinutes((int) env('JWT_REFRESH_TTL', 20160))->timestamp]
        );

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

}
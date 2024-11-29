<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\RegisterResource;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\LoginResource;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\TwoFactorAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $service;

    public function __construct(TwoFactorAuthService $service)
    {
        $this->service = $service;
    }

    // Метод регистрации
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
        ]);

        // Проверка на успешное сохранение
        if ($user) {
            return (new RegisterResource($user))->response()->setStatusCode(201);
        } else {
            return response()->json(['error' => 'User registration error'], 500);
        }
    }

    // Метод авторизации
    public function login(LoginRequest $request) 
    {
        // Поиск пользователя по имени пользователя и проверка пароля
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Если 2FA включена
        if ($user->twoFactorAuth) {

            if ($user->tfa_token === null){
                // Генерация уникального токена
                $bytes = random_bytes(40); // Генерация 40 случайных байтов
                $rawToken = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');

                // Сохранение User-Agent и IP-адреса
                Cache::put('2fa_' . $user->id, [
                    'user_agent' => $request->header('User-Agent'),
                    'ip' => $request->ip(),
                ], now()->addSeconds(30));

                $user->tfa_token = $rawToken;
                $user->save();
            }

            $result = $this->service->generateCode($user);

            if (!$result['success']) {
                $remainingTime = $result['delaySeconds'] - $result['diffInSeconds'];
                return response()->json(['message' => 'Подождите ' . $remainingTime . ' секунд перед следующим запросом'], 429);
            }

            // Отправка кода пользователю
            return response()->json([
                'message' => 'На ваш email отправлен код для подтверждения двухфакторной аутентификации',
                'tfa_token' => $user->tfa_token,
            ]);
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

    // Получение информации о текущем пользователе
    public function me(Request $request)
    {
        // Извлечение пользователя, который был добавлен в middleware
        $user = $request->user();
        // Возвращение информации о пользователе через ресурс UserResource
        return new UserResource($user);
    }

    // Метод для выхода (удаление текущего токена)
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out'], 200);
    }

    // Метод для выхода со всех устройств (удаление всех токенов пользователя)
    public function logoutAll(Request $request)
    {
        UserToken::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'All tokens deleted'], 200);
    }

    // Получение списка токенов пользователя
    public function tokens(Request $request)
    {
        $tokens = UserToken::where('user_id', $request->user()->id)->get();
        return response()->json($tokens);
    }

    public function changePassword(Request $request)
    {
        // Валидируем входные данные
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[@$!%*#?&]/'],
            'new_password_confirmation' => ['required'],  
            // ['same:new_password']
        ]);

        $user = $request->user();        

        // Проверка текущего пароля
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }
        // Проверка, что новый пароль не совпадает со старым
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json(['error' => 'New password must not be the same as the current password'], 400);
        }
        // Проверка, что новый пароль new_password совпадает с new_password_confirmation
        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json(['error' => 'New password and confirmation do not match'], 400);
        }

        // Смена пароля
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Удаление всех активных токенов, чтобы заставить пользователя войти заново
        UserToken::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Password successfully changed, please log in again'], 200);
    }

    public function refresh(Request $request)
    {
        // Получаем рефреш токен из запроса
        $refreshToken = $request->input('refresh_token');

        // Проверяем, является ли токен действительным
        try {
            // Проверяем токен обновления
            $user = JWTAuth::setToken($refreshToken)->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            // Создаем новый основной токен
            $newToken = JWTAuth::fromUser($user);
            
            // Создаем новый одноразовый токен обновления
            $newRefreshToken = JWTAuth::fromUser($user, ['exp' => now()->addMinutes(env('JWT_REFRESH_TTL', 20160))->timestamp]);

            return response()->json([
                'token' => $newToken,
                'refresh_token' => $newRefreshToken,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 500);
        }
    }
}
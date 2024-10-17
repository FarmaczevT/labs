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


class AuthController extends Controller
{
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

        // Проверка количества активных токенов
        $maxTokens = env('MAX_ACTIVE_TOKENS', 4); // Получаем значение из переменной окружения или по умолчанию 5
        $activeTokensCount = UserToken::where('user_id', $user->id)->count();

        if ($activeTokensCount >= $maxTokens) {
            return response()->json(['error' => 'Maximum number of active tokens reached'], 401);
        }

        // Генерация уникального токена
        $bytes = random_bytes(40); // Генерация 40 случайных байтов
        $rawToken = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='); // Кодирование в base64

        $token = base64_encode($user->id . '.' . $rawToken);

        // Сохранение токена в базе данных
        UserToken::create([
            'user_id' => $user->id,
            'token' => $token,
        ]);

        return response()->json([
            'status' => 'Successful authorization',
            'token' => $token,
            'user' => new LoginResource($user),
        ], 200);
    }

    // Получение информации о текущем пользователе
    public function me(Request $request)
    {
        // Извлечение пользователя, который был добавлен в middleware
        $user = $request->user;
        // Возвращение информации о пользователе через ресурс UserResource
        return new UserResource($user);
    }

    // Метод для выхода (удаление текущего токена)
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        UserToken::where('token', $token)->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }

    // Метод для выхода со всех устройств (удаление всех токенов пользователя)
    public function logoutAll(Request $request)
    {
        UserToken::where('user_id', $request->user->id)->delete();

        return response()->json(['message' => 'All tokens deleted'], 200);
    }

    // Получение списка токенов пользователя
    public function tokens(Request $request)
    {
        $tokens = UserToken::where('user_id', $request->user->id)->get();
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

        $user = $request->user;        

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
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckTwoFactorAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Получаем логин из запроса
        $login = $request->input('username'); // Или другой параметр, содержащий логин

        // Ищем пользователя по логину
        $user = User::where('username', $login)->first();

        // Если 2FA отключена, пропускаем запрос
        if (!$user->twoFactorAuth) {
            return $next($request);
        }
    }
}
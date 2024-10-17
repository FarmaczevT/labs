<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserToken;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Authorization token not provided'], 401);
        }

        $token = str_replace('Bearer ', '', $token);
        $userToken = UserToken::where('token', $token)->first();

        if (!$userToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Авторизация пользователя
        $request->merge(['user' => $userToken->user]);

        return $next($request);
    }
}

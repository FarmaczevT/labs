<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Проверка, есть ли у пользователя роль администратора (role_id = 1)
        if (!$user || !$user->roles()->where('role_id', 1)->exists()) {
            return response()->json(['error' => 'Access denied. Admins only'], 403);
        }

        return $next($request);
    }
}
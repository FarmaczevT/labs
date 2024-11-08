<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/out', [AuthController::class, 'logout']);
    Route::get('/auth/tokens', [AuthController::class, 'tokens']);
    Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/change_pass', [AuthController::class, 'changePassword']);
    Route::post('/auth/refresh_token', [AuthController::class, 'refresh']);
});
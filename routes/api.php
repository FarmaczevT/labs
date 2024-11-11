<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
// Жесткое удаление роли (без middleware auth.jwt)
Route::delete('policy/role/{id}', [RoleController::class, 'destroyRole']);
// Жесткое удаление разрешения (без middleware auth.jwt)
Route::delete('policy/permission/{id}', [PermissionController::class, 'destroy']);

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/out', [AuthController::class, 'logout']);
    Route::get('/auth/tokens', [AuthController::class, 'tokens']);
    Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/change_pass', [AuthController::class, 'changePassword']);
    Route::post('/auth/refresh_token', [AuthController::class, 'refresh']);

    // Получение списка ролей
    Route::get('policy/role', [RoleController::class, 'indexRole']);
    // Получение конкретной роли
    Route::get('policy/role/{id}', [RoleController::class, 'showRole']);   
    // Создание роли
    Route::post('policy/role', [RoleController::class, 'storeRole']);
    // Обновление роли
    Route::put('policy/role/{id}', [RoleController::class, 'updateRole']);  
    // Мягкое удаление роли
    Route::delete('policy/role/{id}/soft', [RoleController::class, 'softDeleteRole']);  
    // Восстановление мягко удаленной роли
    Route::post('policy/role/{id}/restore', [RoleController::class, 'restoreRole']);

    // Получение списка разрешений
    Route::get('policy/permission', [PermissionController::class, 'indexPermission']);
    // Получение конкретной разрешения
    Route::get('policy/permission/{id}', [PermissionController::class, 'showPermission']);   
    // Создание разрешения
    Route::post('policy/permission', [PermissionController::class, 'storePermission']);
    // Обновление разрешения
    Route::put('policy/permission/{id}', [PermissionController::class, 'updatePermission']);  
    // Мягкое удаление разрешений
    Route::delete('policy/permission/{id}/soft', [PermissionController::class, 'softDeletePermission']);  
    // Восстановление мягко удаленного разрешения
    Route::post('policy/permission/{id}/restore', [PermissionController::class, 'restorePermission']);
});
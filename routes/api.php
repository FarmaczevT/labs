<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Middleware\CheckPermission;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
// Жесткое удаление роли (без middleware auth.jwt)
Route::delete('policy/role/{id}', [RoleController::class, 'destroyRole'])->middleware(CheckPermission::class . ':DELETE_ROLE');
// Жесткое удаление разрешения (без middleware auth.jwt)
Route::delete('policy/permission/{id}', [PermissionController::class, 'destroyPermission'])->middleware(CheckPermission::class . ':DELETE_PERMISSION');

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/out', [AuthController::class, 'logout']);
    Route::get('/auth/tokens', [AuthController::class, 'tokens']);
    Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/change_pass', [AuthController::class, 'changePassword']);
    Route::post('/auth/refresh_token', [AuthController::class, 'refresh']);

    /* ________________________________________________________________________$$__
    _$$$$__$$$$$$_$$__$$__$$$$___$$$$__$$__$$____$$$$$___$$$$_____$$$_$$$$$$_$$__$$
    $$__$$_$$__$$_$$__$$_$$__$$_$$__$$_$$_$$_____$$__$$_$$__$$___$_$$_$$_____$$__$$
    $$_____$$__$$_$$_$$$_$$_____$$__$$_$$$$______$$$$$$_$$__$$__$__$$_$$$$___$$_$$$
    $$__$$_$$__$$_$$$_$$_$$__$$_$$__$$_$$_$$_____$$_____$$__$$_$$__$$_$$_____$$$_$$
    _$$$$__$$__$$_$$__$$__$$$$___$$$$__$$__$$____$$______$$$$__$$__$$_$$$$$$_$$__$$
    */

    // Получение списка ролей
    Route::get('policy/role', [RoleController::class, 'indexRole'])->middleware(CheckPermission::class . ':GET-LIST_ROLE');
    // Получение конкретной роли
    Route::get('policy/role/{id}', [RoleController::class, 'showRole'])->middleware(CheckPermission::class . ':READ_ROLE');   
    // Создание роли
    Route::post('policy/role', [RoleController::class, 'storeRole'])->middleware(CheckPermission::class . ':CREATE_ROLE');
    // Обновление роли
    Route::put('policy/role/{id}', [RoleController::class, 'updateRole'])->middleware(CheckPermission::class . ':UPDATE_ROLE');  
    // Мягкое удаление роли
    Route::delete('policy/role/{id}/soft', [RoleController::class, 'softDeleteRole'])->middleware(CheckPermission::class . ':DELETE_ROLE'); 
    // Восстановление мягко удаленной роли
    Route::post('policy/role/{id}/restore', [RoleController::class, 'restoreRole'])->middleware(CheckPermission::class . ':RESTORE_ROLE');

    /* ___________________________________________________________________________________________________________$$__
    _$$$$__$$$$$$_$$__$$__$$$$___$$$$__$$__$$____$$$$$___$$$$__$$$$$__$$$$$__$$$$$$_$$___$_$$$$$$_$$__$$_$$__$$_$$__$$
    $$__$$_$$__$$_$$__$$_$$__$$_$$__$$_$$_$$_____$$__$$_$$__$$_____$$_$$__$$_$$_____$$___$_$$_____$$__$$_$$__$$_$$__$$
    $$_____$$__$$_$$_$$$_$$_____$$__$$_$$$$______$$$$$$_$$$$$$___$$$__$$$$$$_$$$$___$$_$_$_$$$$___$$$$$$_$$_$$$_$$_$$$
    $$__$$_$$__$$_$$$_$$_$$__$$_$$__$$_$$_$$_____$$_____$$__$$_____$$_$$_____$$_____$$_$_$_$$_____$$__$$_$$$_$$_$$$_$$
    _$$$$__$$__$$_$$__$$__$$$$___$$$$__$$__$$____$$_____$$__$$_$$$$$__$$_____$$$$$$_$$$$$$_$$$$$$_$$__$$_$$__$$_$$__$$
    */

    // Получение списка разрешений
    Route::get('policy/permission', [PermissionController::class, 'indexPermission'])->middleware(CheckPermission::class . ':GET-LIST_PERMISSION');
    // Получение конкретного разрешения
    Route::get('policy/permission/{id}', [PermissionController::class, 'showPermission'])->middleware(CheckPermission::class . ':READ_PERMISSION');
    // Создание разрешения
    Route::post('policy/permission', [PermissionController::class, 'storePermission'])->middleware(CheckPermission::class . ':CREATE_PERMISSION');
    // Обновление разрешения
    Route::put('policy/permission/{id}', [PermissionController::class, 'updatePermission'])->middleware(CheckPermission::class . ':UPDATE_PERMISSION');
    // Мягкое удаление разрешений
    Route::delete('policy/permission/{id}/soft', [PermissionController::class, 'softDeletePermission'])->middleware(CheckPermission::class . ':DELETE_PERMISSION');
    // Восстановление мягко удаленного разрешения
    Route::post('policy/permission/{id}/restore', [PermissionController::class, 'restorePermission'])->middleware(CheckPermission::class . ':RESTORE_PERMISSION');

    /*
    _$$$$__$$$$$___$$$$$_$$$$$__$$__$$____$$$$$$__$$$$_____$$$_$$____$$$$$___$$$$__$$$$$___$$$$__$$$$$$_$$$$$$____$$$__$$$$$____$$__$$____$$$$$___$$$$_____$$$_$$__$$
    $$__$$_$$__$$_$$__$$_____$$_$$__$$____$$__$$_$$__$$___$_$$_$$________$$_$$__$$_$$__$$_$$__$$___$$___$$_______$_$$_$$__$$____$$__$$____$$__$$_$$__$$___$_$$_$$__$$
    $$_____$$$$$___$$$$$___$$$__$$_$$$____$$__$$_$$__$$__$__$$_$$$$$___$$$__$$__$$_$$$$$__$$$$$$___$$___$$$$____$__$$__$$$$$____$$_$$$____$$$$$$_$$__$$__$__$$_$$_$$$
    $$__$$_$$__$$_$$__$$_____$$_$$$_$$____$$__$$_$$__$$_$$__$$_$$_$$_____$$_$$__$$_$$__$$_$$__$$___$$___$$_____$$__$$_$$__$$____$$$_$$____$$_____$$__$$_$$__$$_$$$_$$
    _$$$$__$$$$$__$$__$$_$$$$$__$$__$$____$$__$$__$$$$__$$__$$_$$$$$_$$$$$___$$$$__$$$$$__$$__$$___$$___$$$$$$_$$__$$_$$__$$____$$__$$____$$______$$$$__$$__$$_$$__$$
    */

    // Получение списка пользователей
    Route::get('policy/users', [UsersController::class, 'UserCollection'])->middleware(CheckPermission::class . ':GET-LIST_USER');
    // Получение конкретного пользователя
    Route::get('policy/user/{id}', [UsersController::class, 'showUser'])->middleware(CheckPermission::class . ':READ_USER');  
    // Создание связи пользователя и роли
    Route::post('policy/user/{user_id}/role/{role_id}', [UsersController::class, 'storeUserRole'])->middleware(CheckPermission::class . ':CREATE_USER');
    // Жесткое удаление связи пользователя и роли
    Route::delete('policy/userRole/{id}', [UsersController::class, 'destroyUserRole'])->middleware(CheckPermission::class . ':DELETE_USER'); 
    // Мягкое удаление связи пользователя и роли
    Route::delete('policy/userRole/{id}/soft', [UsersController::class, 'softDeleteUserRole'])->middleware(CheckPermission::class . ':DELETE_USER'); 
    // Восстановление мягко удаленноq связи пользователя и роли
    Route::post('policy/userRole/{id}/restore', [UsersController::class, 'restoreUserRole'])->middleware(CheckPermission::class . ':RESTORE_USER');

    /*
    _$$$$__$$$$$___$$$$$_$$$$$__$$__$$____$$$$$___$$$$_____$$$_$$__$$_____$$$$_____$$$$$___$$$$__$$$$$__$$$$$__$$$$$$_$$___$_$$$$$$_$$__$$_$$__$$__$$$$$_$$___$$_$$__$$
    $$__$$_$$__$$_$$__$$_____$$_$$__$$____$$__$$_$$__$$___$_$$_$$__$$____$$__$$____$$__$$_$$__$$_____$$_$$__$$_$$_____$$___$_$$_____$$__$$_$$__$$_$$__$$_$$$_$$$_$$__$$
    $$_____$$$$$___$$$$$___$$$__$$_$$$____$$$$$$_$$__$$__$__$$_$$_$$$____$$________$$$$$$_$$$$$$___$$$__$$$$$$_$$$$___$$_$_$_$$$$___$$$$$$_$$_$$$__$$$$$_$$_$_$$_$$_$$$
    $$__$$_$$__$$_$$__$$_____$$_$$$_$$____$$_____$$__$$_$$__$$_$$$_$$____$$__$$____$$_____$$__$$_____$$_$$_____$$_____$$_$_$_$$_____$$__$$_$$$_$$_$$__$$_$$___$$_$$$_$$
    _$$$$__$$$$$__$$__$$_$$$$$__$$__$$____$$______$$$$__$$__$$_$$__$$_____$$$$_____$$_____$$__$$_$$$$$__$$_____$$$$$$_$$$$$$_$$$$$$_$$__$$_$$__$$_$$__$$_$$___$$_$$__$$
    */
    
    // Получение конкретной связи роли с разрешениями
    Route::get('policy/rolePermission/{role_id}', [RolePermissionController::class, 'showRolePermission'])->middleware(CheckPermission::class . ':READ_ROLE'); 
    // Создание связи роли с разрешениями
    Route::post('policy/role/{role_id}/permission/{permission_id}', [RolePermissionController::class, 'storeRolePermission'])->middleware(CheckPermission::class . ':CREATE_ROLE');
    // Жесткое удаление связи роли с разрешениями
    Route::delete('policy/rolePermission/{id}', [RolePermissionController::class, 'destroyRolePermission'])->middleware(CheckPermission::class . ':DELETE_ROLE');
    // Мягкое удаление связи роли с разрешениями
    Route::delete('policy/rolePermission/{id}/soft', [RolePermissionController::class, 'softDeleteRolePermission'])->middleware(CheckPermission::class . ':DELETE_ROLE');
    // Восстановление мягко удаленной связи роли с разрешениями
    Route::post('policy/rolePermission/{id}/restore', [RolePermissionController::class, 'restoreRolePermission'])->middleware(CheckPermission::class . ':RESTORE_ROLE');
});
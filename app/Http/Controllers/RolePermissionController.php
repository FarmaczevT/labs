<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolePermissionRequest\RolePermissionRequest;
use App\Http\Resources\RolePermissionResource;
use App\Models\RolePermission;
use App\DTO\RolePermission_DTO\RolePermissionDTO;
use App\DTO\RolePermission_DTO\RolePermissionCollectionDTO;

    /*
    _$$$$__$$$$$___$$$$$_$$$$$__$$__$$____$$$$$___$$$$_____$$$_$$__$$_____$$$$_____$$$$$___$$$$__$$$$$__$$$$$__$$$$$$_$$___$_$$$$$$_$$__$$_$$__$$__$$$$$_$$___$$_$$__$$
    $$__$$_$$__$$_$$__$$_____$$_$$__$$____$$__$$_$$__$$___$_$$_$$__$$____$$__$$____$$__$$_$$__$$_____$$_$$__$$_$$_____$$___$_$$_____$$__$$_$$__$$_$$__$$_$$$_$$$_$$__$$
    $$_____$$$$$___$$$$$___$$$__$$_$$$____$$$$$$_$$__$$__$__$$_$$_$$$____$$________$$$$$$_$$$$$$___$$$__$$$$$$_$$$$___$$_$_$_$$$$___$$$$$$_$$_$$$__$$$$$_$$_$_$$_$$_$$$
    $$__$$_$$__$$_$$__$$_____$$_$$$_$$____$$_____$$__$$_$$__$$_$$$_$$____$$__$$____$$_____$$__$$_____$$_$$_____$$_____$$_$_$_$$_____$$__$$_$$$_$$_$$__$$_$$___$$_$$$_$$
    _$$$$__$$$$$__$$__$$_$$$$$__$$__$$____$$______$$$$__$$__$$_$$__$$_____$$$$_____$$_____$$__$$_$$$$$__$$_____$$$$$$_$$$$$$_$$$$$$_$$__$$_$$__$$_$$__$$_$$___$$_$$__$$
    */

class RolePermissionController extends Controller
{
    // Получение всех связей для конкретной роли по ID роли
    public function showRolePermission($roleId)
    {
        // Извлекаем все связи для конкретной роли по role_id
        $rolePermissions = RolePermission::where('role_id', $roleId)->get();

        // Преобразуем коллекцию моделей RolePermission в массив DTO
        $rolePermissionDTOs = $rolePermissions->map(function ($rolePermission) {
            return new RolePermissionDTO(
                $rolePermission->permission_id,
                $rolePermission->role_id,
                $rolePermission->created_by
            );
        })->toArray();

        // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
        $rolePermissionCollectionDTO = new RolePermissionCollectionDTO($rolePermissionDTOs);

        // Возвращаем результат
        return $rolePermissionCollectionDTO->toArray();
    }


    // Создание новой связи роли с разрешениями
    public function storeRolePermission(RolePermissionRequest $request)
    {
        // Получаем DTO из данных запроса
        $rolePermissionDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $rolePermission = RolePermission::create($rolePermissionDTO->toArray());

        return (new RolePermissionResource($rolePermission))->response()->setStatusCode(201);
    }

    // Жесткое удаление связи роли с разрешениями
    public function destroyRolePermission($id)
    {
        // Находим связи пользователя и роли по ID
        $rolePermission = RolePermission::find($id);

        // Проверяем, существует ли роль
        if (!$rolePermission) {
            return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
        }

        // Выполняем жесткое удаление
        $rolePermission->forceDelete();

        return response()->json(['message' => 'The permissions connection to the role permanently deleted'], 200);
    }

    // Мягкое удаление связи роли с разрешениями
    public function softDeleteRolePermission($id)
    {
        // Находим роль по ID
        $rolePermission = RolePermission::find($id);
        // Проверяем, существует ли роль
        if (!$rolePermission) {
            return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
        }
        $rolePermission->delete(); // Использует soft delete
        return response()->json(['message' => 'The permissions connection to the role soft deleted'], 200);
    }

    // Восстановление мягко удаленной связи роли с разрешениями
    public function restoreRolePermission($id)
    {
        $rolePermission = RolePermission::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$rolePermission) {
            return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
        }
        $rolePermission->restore();
        return response()->json(['message' => 'The permissions connection to the role restored'], 200);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionRequest\StorePermissionRequest;
use App\Http\Requests\PermissionRequest\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\DTO\Permission_DTO\PermissionDTO;
use App\DTO\Permission_DTO\PermissionCollectionDTO;

    /* ___________________________________________________________________________________________________________$$__
    _$$$$__$$$$$$_$$__$$__$$$$___$$$$__$$__$$____$$$$$___$$$$__$$$$$__$$$$$__$$$$$$_$$___$_$$$$$$_$$__$$_$$__$$_$$__$$
    $$__$$_$$__$$_$$__$$_$$__$$_$$__$$_$$_$$_____$$__$$_$$__$$_____$$_$$__$$_$$_____$$___$_$$_____$$__$$_$$__$$_$$__$$
    $$_____$$__$$_$$_$$$_$$_____$$__$$_$$$$______$$$$$$_$$$$$$___$$$__$$$$$$_$$$$___$$_$_$_$$$$___$$$$$$_$$_$$$_$$_$$$
    $$__$$_$$__$$_$$$_$$_$$__$$_$$__$$_$$_$$_____$$_____$$__$$_____$$_$$_____$$_____$$_$_$_$$_____$$__$$_$$$_$$_$$$_$$
    _$$$$__$$__$$_$$__$$__$$$$___$$$$__$$__$$____$$_____$$__$$_$$$$$__$$_____$$$$$$_$$$$$$_$$$$$$_$$__$$_$$__$$_$$__$$
    */

class PermissionController extends Controller
{
    // Получение списка разрешений
    public function indexPermission()
    {
        $permissions = Permission::all()->toArray(); // Получаем массив ролей из базы данных
        $permissionCollectionDTO = new PermissionCollectionDTO($permissions); // Создаем коллекцию DTO

        return response()->json($permissionCollectionDTO->toArray()); // Возвращаем JSON
    }

    // Получение конкретного разрешения по ID
    public function showPermission($id)
    {
        // Извлекаем роль по id
        $permission = Permission::findOrFail($id);
        // Преобразуем модель Role в DTO
        $permissionDTO = new PermissionDTO(
            $permission->name,
            $permission->description,
            $permission->code,
            $permission->created_by
        );
    
        // Возвращаем DTO через PermissionResource
        return new PermissionResource($permissionDTO);
    }

    // Создание нового разрешения
    public function storePermission(StorePermissionRequest $request)
    {
        // Получаем DTO из данных запроса
        $permissionDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $permission = Permission::create($permissionDTO -> toArray());

        return (new PermissionResource($permission))->response()->setStatusCode(201);
    }

    // Обновление существующего разрешения
    public function updatePermission(UpdatePermissionRequest $request, $id)
    {
        // Находим модель по ID
        $permission = Permission::findOrFail($id);
        $permissionDTO = $request->toPermissionDTO();  // Получение DTO из запроса
        $permission->update($permissionDTO -> toArray());
        return response()->json(new PermissionResource($permission), 200);
    }

    // Жесткое удаление роли по ID
    public function destroyPermission($id)
    {
        // Находим роль по ID
        $permission = Permission::find($id);

        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        // Выполняем жесткое удаление
        $permission->forceDelete();

        return response()->json(['message' => 'Permission permanently deleted'], 200);
    }

    // Мягкое удаление роли
    public function softDeletePermission($id)
    {
        // Находим роль по ID
        $permission = Permission::find($id);
        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $permission->delete(); // Использует soft delete
        return response()->json(['message' => 'Permission soft deleted'], 200);
    }

    // Восстановление мягко удаленной роли
    public function restorePermission($id)
    {
        $permission = Permission::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $permission->restore();
        return response()->json(['message' => 'Permission restored'], 200);
    }
}


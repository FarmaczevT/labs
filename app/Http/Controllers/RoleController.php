<?php

namespace App\Http\Controllers;

use App\DTO\ChangeLog_DTO\ChangeLogDTO;
use App\DTO\ChangeLog_DTO\ChangeLogCollectionDTO;
use App\Models\Role;
use App\Http\Requests\RoleRequest\StoreRoleRequest;
use App\Http\Requests\RoleRequest\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\DTO\Role_DTO\RoleDTO;
use App\DTO\Role_DTO\RoleCollectionDTO;
use App\Models\ChangeLog;

    /* ________________________________________________________________________$$__
    _$$$$__$$$$$$_$$__$$__$$$$___$$$$__$$__$$____$$$$$___$$$$_____$$$_$$$$$$_$$__$$
    $$__$$_$$__$$_$$__$$_$$__$$_$$__$$_$$_$$_____$$__$$_$$__$$___$_$$_$$_____$$__$$
    $$_____$$__$$_$$_$$$_$$_____$$__$$_$$$$______$$$$$$_$$__$$__$__$$_$$$$___$$_$$$
    $$__$$_$$__$$_$$$_$$_$$__$$_$$__$$_$$_$$_____$$_____$$__$$_$$__$$_$$_____$$$_$$
    _$$$$__$$__$$_$$__$$__$$$$___$$$$__$$__$$____$$______$$$$__$$__$$_$$$$$$_$$__$$
    */

class RoleController extends Controller
{
    // Получение списка ролей
    public function indexRole()
    {
        $roles = Role::all()->toArray(); // Получаем массив ролей из базы данных
        $roleCollectionDTO = new RoleCollectionDTO($roles); // Создаем коллекцию DTO

        return response()->json($roleCollectionDTO->toArray()); // Возвращаем JSON
    }

    // Получение конкретной роли по ID
    public function showRole($id)
    {
        // Извлекаем роль по id
        $role = Role::findOrFail($id);
        // Преобразуем модель Role в DTO
        $roleDTO = new RoleDTO(
            $role->name,
            $role->description,
            $role->code,
            $role->created_by
        );
    
        // Возвращаем DTO через RoleResource
        return new RoleResource($roleDTO);
    }

    // Создание новой роли
    public function storeRole(StoreRoleRequest $request)
    {
        // Получаем DTO из данных запроса
        $roleDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $role = Role::create($roleDTO->toArray());

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    // Обновление существующей роли
    public function updateRole(UpdateRoleRequest $request, $id)
    {
        // Находим модель по ID
        $role = Role::findOrFail($id);
        $roleDTO = $request->toRoleDTO();  // Получение DTO из запроса
        $role->update($roleDTO->toArray());
        return response()->json(new RoleResource($role), 200);
    }

    // Жесткое удаление роли по ID
    public function destroyRole($id)
    {
        // Находим роль по ID
        $role = Role::find($id);

        // Проверяем, существует ли роль
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Выполняем жесткое удаление
        $role->forceDelete();

        return response()->json(['message' => 'Role permanently deleted'], 200);
    }

    // Мягкое удаление роли
    public function softDeleteRole($id)
    {
        // Находим роль по ID
        $role = Role::find($id);
        // Проверяем, существует ли роль
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        $role->delete(); // Использует soft delete
        return response()->json(['message' => 'Role soft deleted'], 200);
    }

    // Восстановление мягко удаленной роли
    public function restoreRole($id)
    {
        $role = Role::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        $role->restore();
        return response()->json(['message' => 'Role restored'], 200);
    }

    // Получение истории изменения записи роли по id
    public function roleStory($entityId)
    {
        // Извлекаем все связи для конкретной роли по role_id
        $roles = ChangeLog::where('entity_id', $entityId)->get();

        // Преобразуем коллекцию моделей RolePermission в массив DTO
        $roleDTOs = $roles->map(function ($roleLog) {
            return new ChangeLogDTO(
                $roleLog->entity_name,
                $roleLog->entity_id,
                $roleLog->before,
                $roleLog->after,
                $roleLog->created_by,
            );
        })->toArray();

        // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
        $changeLogCollectionDTO = new ChangeLogCollectionDTO($roleDTOs);

        // Возвращаем результат
        return $changeLogCollectionDTO->toArray();
    }
}


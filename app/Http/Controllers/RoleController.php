<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\DTO\RoleDTO;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    // Получение списка ролей
    public function indexRole()
    {
        $roles = Role::all();
        return RoleResource::collection($roles);
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
        $role = Role::create([
            'name' => $roleDTO->name,
            'description' => $roleDTO->description,
            'code' => $roleDTO->code,
            'created_by' => $roleDTO->created_by,
        ]);

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    // Обновление существующей роли
    public function updateRole(UpdateRoleRequest $request, $id)
    {
        // Находим модель по ID
        $role = Role::findOrFail($id);
        $roleDTO = $request->toRoleDTO();  // Получение DTO из запроса
        $role->update([
            'name' => $roleDTO->name,
            'description' => $roleDTO->description,
            'code' => $roleDTO->code,
            'created_by' => $roleDTO->created_by,
        ]);
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
}


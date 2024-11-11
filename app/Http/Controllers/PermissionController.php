<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // Получение списка ролей
    public function indexPermission(Request $request)
    {
        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    // Получение конкретной роли по ID
    public function showPermissione(Request $request, Permission $permission)
    {
        return new PermissionResource($permission);
    }

    // Создание новой роли
    public function storePermission(StorePermissionRequest $request)
    {
        $permissionDTO = $request->toDTO();  // Получение DTO из запроса
        $permission = Permission::create($permissionDTO->toArray());
        return response()->json(new PermissionResource($permission), 201);
    }

    // Обновление существующей роли
    public function updatePermission(UpdatePermissionRequest $request, Permission $permission)
    {
        $permissionDTO = $request->toDTO();  // Получение DTO из запроса
        $permission->update($permissionDTO->toArray());
        return response()->json(new PermissionResource($permission), 200);
    }

    // Жесткое удаление роли
    public function destroyPermission(Request $request, Permission $permission)
    {
        $permission->forceDelete();
        return response()->json(['message' => 'Permission permanently deleted'], 200);
    }

    // Мягкое удаление роли
    public function softDeletePermission(Request $request, Permission $permission)
    {
        $permission->delete(); // Использует soft delete
        return response()->json(['message' => 'Permission soft deleted'], 200);
    }

    // Восстановление мягко удаленной роли
    public function restorePermission(Request $request, $id)
    {
        $permission = Permission::onlyTrashed()->findOrFail($id);
        $permission->restore();
        return response()->json(['message' => 'Permission restored'], 200);
    }
}


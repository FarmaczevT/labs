<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionRequest\StorePermissionRequest;
use App\Http\Requests\PermissionRequest\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\DTO\Permission_DTO\PermissionDTO;
use App\DTO\Permission_DTO\PermissionCollectionDTO;
use App\DTO\ChangeLog_DTO\ChangeLogDTO;
use App\DTO\ChangeLog_DTO\ChangeLogCollectionDTO;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ChangeLogResource;

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
        DB::beginTransaction(); // Начинаем транзакцию

        try {
        // Получаем DTO из данных запроса
        $permissionDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $permission = Permission::create($permissionDTO -> toArray());

        DB::commit(); // Подтверждаем транзакцию

        return (new PermissionResource($permission))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Failed to store permission'], 500);
        }
    }

    // Обновление существующего разрешения
    public function updatePermission(UpdatePermissionRequest $request, $id)
    {
        DB::beginTransaction(); // Начинаем транзакцию

        try {
        // Находим модель по ID
        $permission = Permission::findOrFail($id);
        $permissionDTO = $request->toPermissionDTO();  // Получение DTO из запроса
        $permission->update($permissionDTO -> toArray());

        DB::commit(); // Подтверждаем транзакцию

        return response()->json(new PermissionResource($permission), 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Failed to update permission'], 500);
        }
    }

    // Жесткое удаление роли по ID
    public function destroyPermission($id)
    {
        DB::beginTransaction(); // Начинаем транзакцию

        try {
        // Находим роль по ID
        $permission = Permission::find($id);

        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        // Выполняем жесткое удаление
        $permission->forceDelete();

        DB::commit(); // Подтверждаем транзакцию

        return response()->json(['message' => 'Permission permanently deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Failed to delete permission'], 500);
        }
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

    // Получение истории изменения записи разрешения по id
    public function permissionStory($entityId)
    {
        // Извлекаем все связи для конкретной роли по role_id
        $permissions = ChangeLog::where('entity_id', $entityId)->get();

        // Преобразуем коллекцию моделей RolePermission в массив DTO
        $permissionsDTOs = $permissions->map(function ($permissionLog) {
            return new ChangeLogDTO(
                $permissionLog->entity_name,
                $permissionLog->entity_id,
                $permissionLog->before,
                $permissionLog->after,
                $permissionLog->created_by,
            );
        })->toArray();

        // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
        $changeLogCollectionDTO = new ChangeLogCollectionDTO($permissionsDTOs);

        // Возвращаем результат
        return response()->json(new ChangeLogResource($changeLogCollectionDTO->toArray()), 200);
    }
}
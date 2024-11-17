<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleRequest\UserRoleRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserRoleResource;
use App\Models\User;
use App\Models\UserRole;
use App\DTO\User_DTO\UserDTO;
use App\DTO\User_DTO\UserCollectionDTO;
use App\DTO\ChangeLog_DTO\ChangeLogDTO;
use App\DTO\ChangeLog_DTO\ChangeLogCollectionDTO;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ChangeLogResource;

/*
    _$$$$__$$$$$___$$$$$_$$$$$__$$__$$____$$$$$$__$$$$_____$$$_$$____$$$$$___$$$$__$$$$$___$$$$__$$$$$$_$$$$$$____$$$__$$$$$____$$__$$____$$$$$___$$$$_____$$$_$$__$$
    $$__$$_$$__$$_$$__$$_____$$_$$__$$____$$__$$_$$__$$___$_$$_$$________$$_$$__$$_$$__$$_$$__$$___$$___$$_______$_$$_$$__$$____$$__$$____$$__$$_$$__$$___$_$$_$$__$$
    $$_____$$$$$___$$$$$___$$$__$$_$$$____$$__$$_$$__$$__$__$$_$$$$$___$$$__$$__$$_$$$$$__$$$$$$___$$___$$$$____$__$$__$$$$$____$$_$$$____$$$$$$_$$__$$__$__$$_$$_$$$
    $$__$$_$$__$$_$$__$$_____$$_$$$_$$____$$__$$_$$__$$_$$__$$_$$_$$_____$$_$$__$$_$$__$$_$$__$$___$$___$$_____$$__$$_$$__$$____$$$_$$____$$_____$$__$$_$$__$$_$$$_$$
    _$$$$__$$$$$__$$__$$_$$$$$__$$__$$____$$__$$__$$$$__$$__$$_$$$$$_$$$$$___$$$$__$$$$$__$$__$$___$$___$$$$$$_$$__$$_$$__$$____$$__$$____$$______$$$$__$$__$$_$$__$$
    */

class UsersController extends Controller
{
    // Получение списка пользователей
    public function UserCollection()
    {
        $users = User::all()->toArray(); // Получаем массив ролей из базы данных
        $userCollectionDTO = new UserCollectionDTO($users); // Создаем коллекцию DTO

        return response()->json($userCollectionDTO->toArray()); // Возвращаем JSON
    }

    // Получение конкретной пользователя по ID
    public function showUser($id)
    {
        // Извлекаем роль по id
        $user = User::findOrFail($id);
        // Преобразуем модель Role в DTO
        $userDTO = new UserDTO(
            $user->id,
            $user->username,
            $user->email,
            $user->birthday
        );
    
        // Возвращаем DTO через RoleResource
        return new UserResource($userDTO);
    }

    // Создание новой связи пользователя и роли
    public function storeUserRole(UserRoleRequest $request)
    {
        DB::beginTransaction(); // Начинаем транзакцию

        try {
        // Получаем DTO из данных запроса
        $userRoleDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $userRole = UserRole::create($userRoleDTO->toArray());

        DB::commit(); // Подтверждаем транзакцию

        return (new UserRoleResource($userRole))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Failed to store user-role association'], 500);
        }
    }

    // Жесткое удаление связи пользователя и роли
    public function destroyUserRole($id)
    {
        DB::beginTransaction();

        try {
            // Находим связь пользователя и роли по ID
            $userRole = UserRole::find($id);

            if (!$userRole) {
                return response()->json(['message' => 'The user-role connection was not found'], 404);
            }

            // Выполняем жесткое удаление
            $userRole->forceDelete();

            DB::commit();

            return response()->json(['message' => 'The user-role connection permanently deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete user-role connection'], 500);
        }
    }


    // Мягкое удаление связи пользователя и роли
    public function softDeleteUserRole($id)
    {
        // Находим роль по ID
        $userRole = UserRole::find($id);
        // Проверяем, существует ли роль
        if (!$userRole) {
            return response()->json(['message' => 'The users connection to the role was not found'], 404);
        }
        $userRole->delete(); // Использует soft delete
        return response()->json(['message' => 'The users connection to the role soft deleted'], 200);
    }

    // Восстановление мягко удаленноq связи пользователя и роли
    public function restoreUserRole($id)
    {
        $userRole = UserRole::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$userRole) {
            return response()->json(['message' => 'The users connection to the role was not found'], 404);
        }
        $userRole->restore();
        return response()->json(['message' => 'The users connection to the role restored'], 200);
    }

    // Получение истории изменения записи пользователя по id
    public function userStory($entityId)
    {
        // Извлекаем все связи для конкретной роли по role_id
        $users = ChangeLog::where('entity_id', $entityId)->get();

        // Преобразуем коллекцию моделей RolePermission в массив DTO
        $usersDTOs = $users->map(function ($userLog) {
            return new ChangeLogDTO(
                $userLog->entity_name,
                $userLog->entity_id,
                $userLog->before,
                $userLog->after,
                $userLog->created_by,
            );
        })->toArray();

        // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
        $changeLogCollectionDTO = new ChangeLogCollectionDTO($usersDTOs);

        // Возвращаем результат
        return response()->json(new ChangeLogResource($changeLogCollectionDTO->toArray()), 200);
    }
}
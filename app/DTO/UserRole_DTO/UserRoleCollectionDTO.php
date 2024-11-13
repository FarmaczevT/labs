<?php

namespace App\DTO\UserRole_DTO;

class UserRoleCollectionDTO
{
    private array $userRoles;

    public function __construct(array $userRoles)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->userRoles = array_map(function($userRole) {
            return new UserRoleDTO(
                $userRole['user_id'],
                $userRole['role_id'],
                $userRole['created_by'],
            );
        }, $userRoles);
    }

    public function toArray(): array
    {
        return array_map(fn(UserRoleDTO $userRole) => $userRole->toArray(), $this->userRoles);
    }
}
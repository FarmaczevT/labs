<?php

namespace App\DTO\RolePermission_DTO;

class RolePermissionCollectionDTO
{
    private array $rolePermissions;

    public function __construct(array $rolePermissions)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->rolePermissions = array_map(function($rolePermission) {
            return new RolePermissionDTO(
                $rolePermission['permission_id'],
                $rolePermission['role_id'],
                $rolePermission['created_by'],
            );
        }, $rolePermissions);
    }

    public function toArray(): array
    {
        // Преобразуем каждый RolePermissionDTO в массив
        return array_map(fn(RolePermissionDTO $rolePermission) => $rolePermission->toArray(), $this->rolePermissions);
    }
}
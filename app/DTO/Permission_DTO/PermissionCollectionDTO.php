<?php

namespace App\DTO\Permission_DTO;

class PermissionCollectionDTO
{
    private array $permissions;

    public function __construct(array $permissions)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->permissions = array_map(function($permission) {
            return new PermissionDTO(
                $permission['name'],
                $permission['description'],
                $permission['code'],
                $permission['created_by'] ?? null
            );
        }, $permissions);
    }

    public function toArray(): array
    {
        return array_map(fn(PermissionDTO $permission) => $permission->toArray(), $this->permissions);
    }
}
<?php

namespace App\DTO\Role_DTO;

class RoleCollectionDTO
{
    private array $roles;

    public function __construct(array $roles)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->roles = array_map(function($role) {
            return new RoleDTO(
                $role['name'],
                $role['description'],
                $role['code'],
                $role['created_by'] ?? null
            );
        }, $roles);
    }

    public function toArray(): array
    {
        return array_map(fn(RoleDTO $role) => $role->toArray(), $this->roles);
    }
}
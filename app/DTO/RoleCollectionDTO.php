<?php

namespace App\DTO;

class RoleCollectionDTO
{
    private array $roles;

    public function __construct(array $roles)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->roles = array_map(function($role) {
            return new RoleDTO(
                $role['name'],  // Предполагаем, что элементы массива имеют ключи 'name', 'description', 'code'
                $role['description'],
                $role['code'],
                $role['created_by'] ?? null // Используем, если есть ключ 'created_by'
            );
        }, $roles);
    }

    public function toArray(): array
    {
        return array_map(fn(RoleDTO $role) => $role->toArray(), $this->roles);
    }
}

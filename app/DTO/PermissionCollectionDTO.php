<?php

namespace App\DTO;

class PermissionCollectionDTO
{
    private array $permissions;

    public function __construct(array $permissions)
    {
        $this->permissions = array_map(fn($permission) => new PermissionDTO($permission), $permissions);
    }

    public function toArray(): array
    {
        return array_map(fn(PermissionDTO $permission) => $permission->toArray(), $this->permissions);
    }
}
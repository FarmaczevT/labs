<?php

namespace App\DTO\Permission_DTO;

class PermissionCollectionDTO
{
    private array $permissions;

    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    public function toArray(): array
    {
        return array_map(fn(PermissionDTO $permission) => $permission->toArray(), $this->permissions);
    }
}
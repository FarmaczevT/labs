<?php

namespace App\DTO\Role_DTO;

class RoleCollectionDTO
{
    private array $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    public function toArray(): array
    {
        return array_map(fn(RoleDTO $role) => $role->toArray(), $this->roles);
    }
}
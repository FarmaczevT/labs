<?php

namespace App\DTO\User_DTO;

class UserCollectionDTO
{
    private array $users;

    public function __construct(array $users)
    {
        $this->users = $users;
    }

    public function toArray(): array
    {
        return array_map(fn(UserDTO $user) => $user->toArray(), $this->users);
    }
}
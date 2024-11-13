<?php

namespace App\DTO\User_DTO;

class UserCollectionDTO
{
    private array $users;

    public function __construct(array $users)
    {
        // Передаем каждый элемент массива в конструктор RoleDTO
        $this->users = array_map(function($user) {
            return new UserDTO(
                $user['id'],
                $user['username'],
                $user['email'],
                $user['birthday'],
            );
        }, $users);
    }

    public function toArray(): array
    {
        return array_map(fn(UserDTO $user) => $user->toArray(), $this->users);
    }
}
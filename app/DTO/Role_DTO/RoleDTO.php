<?php

namespace App\DTO\Role_DTO;

class RoleDTO
{
    public $name;
    public $description;
    public $code;
    public $created_by;

    public function __construct($name, $description = null, $code, $created_by)
    {
        $this->name = $name;
        $this->description = $description;
        $this->code = $code;
        $this->created_by = $created_by;
    }

    // Метод для преобразования DTO в массив
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'created_by' => $this->created_by,
        ];
    }
}
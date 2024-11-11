<?php

namespace App\DTO;

class PermissionDTO
{
    public string $name;
    public string $code;
    public ?string $description;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->code = $data['code'];
        $this->description = $data['description'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
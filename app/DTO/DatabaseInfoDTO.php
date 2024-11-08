<?php
namespace App\DTO;

class DatabaseInfoDTO {
    public $database_name;
    public $driver;

    public function __construct($database_name, $driver) {
        $this->database_name = $database_name;
        $this->driver = $driver;
    }
}
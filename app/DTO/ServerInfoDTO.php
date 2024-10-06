<?php
namespace App\DTO;

class ServerInfoDTO {
    public $php_version;
    public $server_info;

    public function __construct($php_version, $server_info) {
        $this->php_version = $php_version;
        $this->server_info = $server_info;
    }
}
<?php
namespace App\DTO;

class ClientInfoDTO {
    public $ip;
    public $user_agent;

    public function __construct($ip, $user_agent) {
        $this->ip = $ip;
        $this->user_agent = $user_agent;
    }
}
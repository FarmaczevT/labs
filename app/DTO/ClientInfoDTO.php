<?php
namespace App\DTO;

class ClientInfoDTO {
    public $ip;
    public $user_agent;
    public $sec_ch_ua;
    public $document_root;

    public function __construct($ip, $user_agent, $sec_ch_ua, $document_root) {
        $this->ip = $ip;
        $this->user_agent = $user_agent;
        $this->sec_ch_ua = $sec_ch_ua;
        $this->document_root = $document_root;
    }
}
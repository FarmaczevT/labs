<?php

namespace App\DTO\LogRequest_DTO;

class LogRequestDTO
{
    public string $url;
    public string $http_method;
    public ?string $controller;
    public ?string $controller_method;
    public array $request_body;
    public array $request_headers;
    public ?int $user_id;
    public string $user_ip;
    public ?string $user_agent;
    public int $response_status;
    public array $response_body;
    public array $response_headers;
    public string $requested_at;

    public function __construct(array $attributes)
    {
        $this->url = $attributes['url'];
        $this->http_method = $attributes['http_method'];
        $this->controller = $attributes['controller'] ?? null;
        $this->controller_method = $attributes['controller_method'] ?? null;
        $this->request_body = $attributes['request_body'] ?? [];
        $this->request_headers = $attributes['request_headers'] ?? [];
        $this->user_id = $attributes['user_id'] ?? null;
        $this->user_ip = $attributes['user_ip'];
        $this->user_agent = $attributes['user_agent'] ?? null;
        $this->response_status = $attributes['response_status'];
        $this->response_body = $attributes['response_body'] ?? [];
        $this->response_headers = $attributes['response_headers'] ?? [];
        $this->requested_at = $attributes['requested_at'];
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'http_method' => $this->http_method,
            'controller' => $this->controller,
            'controller_method' => $this->controller_method,
            'request_body' => $this->request_body,
            'request_headers' => $this->request_headers,
            'user_id' => $this->user_id,
            'user_ip' => $this->user_ip,
            'user_agent' => $this->user_agent,
            'response_status' => $this->response_status,
            'response_body' => $this->response_body,
            'response_headers' => $this->response_headers,
            'requested_at' => $this->requested_at,
        ];
    }
}

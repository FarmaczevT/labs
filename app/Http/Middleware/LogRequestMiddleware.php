<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LogRequest;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $logData = [
            'url' => $request->fullUrl(),
            'http_method' => $request->method(),
            'controller' => $this->getController($request),
            'controller_method' => $this->getControllerMethod($request),
            'request_body' => $request->all(),
            'request_headers' => $request->headers->all(),
            'user_id' => $request->user()->id,
            'user_ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'response_status' => $response->status(),
            'response_body' => $response->getContent(),
            'response_headers' => $response->headers->all(),
            'requested_at' => now(),
        ];

        LogRequest::create($logData);

        return $response;
    }

    private function getController(Request $request)
    {
        return optional($request->route()->getAction()['controller'] ?? null, function ($action) {
            return explode('@', $action)[0] ?? null;
        });
    }

    private function getControllerMethod(Request $request)
    {
        return optional($request->route()->getAction()['controller'] ?? null, function ($action) {
            return explode('@', $action)[1] ?? null;
        });
    }
}
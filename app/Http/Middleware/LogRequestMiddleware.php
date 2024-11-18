<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LogRequest;
use Illuminate\Support\Facades\Route;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // получаем запрос, передаем в контроллер и получаем ответ
        $response = $next($request);

        $logData = [
            'url' => $request->fullUrl(), // Полный адрес вызванного api метода
            'http_method' => $request->method(), // Метод HTTP запроса
            'controller' => $this->getController($request), // Путь до контроллера, обрабатывающего вызов
            'controller_method' => $this->getControllerMethod($request), // Наименование метода контроллера, обрабатывающего вызов
            'request_body' => $request->all(), // Содержимое тела запроса
            'request_headers' => $request->headers->all(), // Содержимое заголовков запроса
            'user_id' => $request->user()->id, // id  пользователя, вызвавшего метод
            'user_ip' => $request->ip(), // IP адрес пользователя, вызвавшего метод
            'user_agent' => $request->header('User-Agent'), // User-Agent пользователя, вызвавшего метод
            'response_status' => $response->status(), // Код статуса ответа пользователю
            'response_body' => $response->getContent(), // Содержимое тела ответа
            'response_headers' => $response->headers->all(), // Заголовки тела ответа
            'requested_at' => now(), // Время вызова метода
        ];
        // Записываем лог в бд
        LogRequest::create($logData);
        // Возвращаем ответ
        return $response;
    }

    // Извлечение названия контроллера
    // private function getController(Request $request)
    // {
    //     return optional($request->route()->getAction()['controller'] ?? null, function ($action) {
    //         return explode('@', $action)[0] ?? null;
    //     });
    // }
    // Извлечение названия контроллера
    private function getController(Request $request)
    {
        $action = Route::currentRouteAction(); // Получаем строку вида "App\Http\Controllers\UserController@index"
        return $action ? explode('@', $action)[0] : null; // Разделяем строку на 2 части и извлекаем имя контроллера
    }
    // Извлечение названия метода
    private function getControllerMethod(Request $request)
    {
        $action = Route::currentRouteAction(); // Получаем строку вида "App\Http\Controllers\UserController@index"
        return $action ? explode('@', $action)[1] : null; // Извлекаем имя метода
    }
}
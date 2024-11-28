<?php

namespace App\Http\Controllers;

use App\Models\LogRequest;
use Illuminate\Http\Request;

class LogRequestController extends Controller
{
    // Показать список логов с фильтрацией и сортировкой
    public function showLogs(Request $request) 
    {
        // Получаем параметры из запроса
        $filters = $request->get('filter', []);
        $sortRules = $request->get('sortBy', []);
        $page = (int) $request->get('page', 1);
        $count = (int) $request->get('count', 10);

        // Допустимые ключи для фильтрации
        $allowedFilterKeys = ['user_id', 'response_status', 'user_ip', 'user_agent', 'controller', 'http_method', 'created_at', 'controller_method'];
        $allowedSortOrders = ['asc', 'desc'];

        // Приведение ключей фильтров и сортировки к нижнему регистру
        $filters = array_map(fn($filter) => [
            'key' => strtolower($filter['key'] ?? ''),
            'value' => $filter['value'] ?? null
        ], $filters);

        $sortRules = array_map(fn($rule) => [
            'key' => strtolower($rule['key'] ?? ''),
            'order' => strtolower($rule['order'] ?? '')
        ], $sortRules);

        // Сообщения об ошибках
        $errors = [
            'filters' => [],
            'sortKeys' => []
        ];

        // Создаем базовый запрос
        $logs = LogRequest::query();

        // Применение фильтров
        foreach ($filters as $filter) {
            // Проверка доступен ли ключ филтра
            if (in_array($filter['key'], $allowedFilterKeys, true)) {
                // Проверка наличия значения в бд
                if (LogRequest::where($filter['key'], $filter['value'])->exists()) {
                    $logs->where($filter['key'], $filter['value']);
                } else {
                    $errors['filters'][] = "Значение '{$filter['value']}' для ключа '{$filter['key']}' отсутствует в базе данных.";
                }
            } else {
                $errors['filters'][] = "Неверный ключ фильтрации: '{$filter['key']}'. Допустимые ключи: " . implode(', ', $allowedFilterKeys);
            }
        }

        // Применение сортировки
        foreach ($sortRules as $rule) {
            if (in_array($rule['key'], $allowedFilterKeys, true)) {
                if (in_array($rule['order'], $allowedSortOrders, true)) {
                    $logs->orderBy($rule['key'], $rule['order']);
                } else {
                    $errors['sortKeys'][] = "Неверный порядок сортировки для ключа '{$rule['key']}': '{$rule['order']}'. Допустимые значения: " . implode(', ', $allowedSortOrders);
                }
            } else {
                $errors['sortKeys'][] = "Неверный ключ сортировки: '{$rule['key']}'. Допустимые ключи: " . implode(', ', $allowedFilterKeys);
            }
        }

        // Если есть ошибки, возвращаем только их
        if (!empty($errors['filters']) || !empty($errors['sortKeys'])) {
            return response()->json(['errors' => array_filter($errors)], 400);
        }

        // Обработка пагинации
        $paginatedLogs = $logs->paginate(
            $count > 0 ? $count : 10, // Минимум 1 запись на странице
            ['*'],
            'page',
            $page > 0 ? $page : 1   // Минимум первая страница
        );

        // Возвращаем данные логов
        return response()->json($paginatedLogs);
    }

    // Показать конкретный лог по ID
    public function showLogById($id)
    {
        $log = LogRequest::findOrFail($id);
        return response()->json($log);
    }
    // Удалить лог по ID
    public function destroyLog($id)
    {
        LogRequest::findOrFail($id)->delete();
        return response()->json(['message' => 'Log deleted']);
    }
}
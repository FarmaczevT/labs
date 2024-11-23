<?php

namespace App\Http\Controllers;

use App\Models\LogRequest;
use Illuminate\Http\Request;

class LogRequestController extends Controller
{
    // Показать список логов с фильтрацией и сортировкой
    public function showLogs(Request $request)
    {
        $filter = $request->input('filter');
        $sort = $request->input('sortBy'); // asc (ascending) — сортировка в возрастающем порядке, desc (descending) — сортировка в убывающем порядке.

        $logs = LogRequest::when($filter, function ($query, $filter) {
            foreach ($filter as $key => $value) {
                $query->where($key, $value);
            } // key параметр по которому фильтруем, value Значение которому должен соответствовать параметр
        })
        ->when($sort, function ($query, $sort) {
            foreach ($sort as $rule) {
                $query->orderBy($rule['key'], $rule['order']); // key параметр по которому сортируем, order способ сортировки
            }
        })
        ->paginate($request->input('count', 10));

        return response()->json($logs);
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
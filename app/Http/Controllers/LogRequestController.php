<?php

namespace App\Http\Controllers;

use App\Models\LogRequest;
use Illuminate\Http\Request;

class LogRequestController extends Controller
{
    public function showLogs(Request $request)
    {
        $query = LogRequest::query();

        if ($filter = $request->input('filter')) {
            foreach ($filter as $key => $value) {
                $query->where($key, $value);
            }
        }

        if ($sort = $request->input('sortBy')) {
            foreach ($sort as $rule) {
                $query->orderBy($rule['key'], $rule['order']);
            }
        }

        $logs = $query->paginate($request->input('count', 10));

        return response()->json($logs);
    }

    public function showLogById($id)
    {
        $log = LogRequest::findOrFail($id);
        return response()->json($log);
    }

    public function destroyLog($id)
    {
        LogRequest::findOrFail($id)->delete();
        return response()->json(['message' => 'Log deleted']);
    }
}
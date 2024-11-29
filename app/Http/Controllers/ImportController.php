<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'mode' => 'required|in:add_only,update_or_add', // Режимы: только добавление или добавление с обновлением
            'exception_handling' => 'required|in:skip_errors,stop_on_error,rollback_on_error', // Режим обработки исключений
        ]);

        $file = $request->file('file')->getRealPath();
        // Загружаем содержимое файла в объект Spreadsheet
        $spreadsheet = IOFactory::load($file);
        // Возвращаем текущий лист для доступа к данным
        $sheet = $spreadsheet->getActiveSheet();
        // Преобразуем данные в массив
        $rows = $sheet->toArray();

        //  Извлекаем значения параметра mode и exception_handling из запроса
        $mode = $request->input('mode');
        $exceptionHandling = $request->input('exception_handling');

        $status = [];
        $hasErrors = false;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    // Пропуск заголовка
                    continue;
                }

                $data = [
                    'username' => $row[1],
                    'email' => $row[2],
                    'password' => $row[3],
                    'birthday' => $row[4],
                    'photo_id' => $row[5],
                    'tfa_token' => $row[6],
                ];

                $userQuery = User::where('username', $row[1]);

                if ($mode === 'add_only' && $userQuery->exists()) { // add_inly - записываем только новые записи
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'duplicate',
                        'message' => 'Запись содержит дубликат по полю username.',
                    ];
                    continue;
                }

                if ($userQuery->exists() && $mode === 'update_or_add') { // update_or_add - добавление с перезаписью
                    $user = $userQuery->first();
                    $user->update($data);
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'updated',
                        'id' => $user->id,
                    ];
                } else {
                    $user = User::create($data);
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'added',
                        'id' => $user->id,
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {

            $hasErrors = true;
            $status[] = [
                'row' => $index + 1,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            if ($exceptionHandling === 'rollback_on_error' && $hasErrors) { // rollback_on_error - отменяет все изменения при наличии ошибок
                DB::rollBack();
                return response()->json([
                    'message' => 'Импорт отменён из-за ошибок.',
                    'status' => $status,
                ], 400);
            }

            if ($exceptionHandling === 'stop_on_error') { // stop_on_error - останавливает импорт при первой ошибке
                DB::rollBack();
                return response()->json([
                    'message' => 'Импорт прерван из-за ошибки.',
                    'error' => $e->getMessage(),
                ], 400);
            }

            $hasErrors = true;
            $status[] = [
                'row' => $index + 1,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        if ($exceptionHandling === 'skip_errors' || !$hasErrors) { // skip_errors - пропускает строки с ошибками
            DB::commit();
        }

        return response()->json([
            'message' => 'Импорт завершён.',
            'status' => $status,
        ]);
    }

    public function importPermissions(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'mode' => 'required|in:add_only,update_or_add', // Режимы: только добавление или добавление с обновлением
            'exception_handling' => 'required|in:skip_errors,stop_on_error,rollback_on_error', // Режим обработки исключений
        ]);

        $file = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $mode = $request->input('mode');
        $exceptionHandling = $request->input('exception_handling');

        $status = [];
        $hasErrors = false;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    // Пропуск заголовка
                    continue;
                }

                $data = [
                    'name' => $row[1],
                    'description' => $row[2],
                    'code' => $row[3],
                    'created_by' => $row[4],
                ];

                $userQuery = Permission::where('name', $row[1]);

                if ($mode === 'add_only' && $userQuery->exists()) { // add_inly - записываем только новые записи
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'duplicate',
                        'message' => 'Запись содержит дубликат по полю name.',
                    ];
                    continue;
                }

                if ($userQuery->exists() && $mode === 'update_or_add') { // update_or_add - добавление с перезаписью
                    $user = $userQuery->first();
                    $user->update($data);
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'updated',
                        'id' => $user->id,
                    ];
                } else {
                    $user = Permission::create($data);
                    $status[] = [
                        'row' => $index + 1,
                        'status' => 'added',
                        'id' => $user->id,
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            
            $hasErrors = true;
            $status[] = [
                'row' => $index + 1,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            if ($exceptionHandling === 'rollback_on_error' && $hasErrors) { // rollback_on_error - отменяет все изменения при наличии ошибок
                DB::rollBack();
                return response()->json([
                    'message' => 'Импорт отменён из-за ошибок.',
                    'status' => $status,
                ], 400);
            }

            if ($exceptionHandling === 'stop_on_error') { // stop_on_error - останавливает импорт при первой ошибке
                DB::rollBack();
                return response()->json([
                    'message' => 'Импорт прерван из-за ошибки.',
                    'error' => $e->getMessage(),
                ], 400);
            }

            $hasErrors = true;
            $status[] = [
                'row' => $index + 1,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        if ($exceptionHandling === 'skip_errors' || !$hasErrors) { // skip_errors - пропускает строки с ошибками
            DB::commit();
        }

        return response()->json([
            'message' => 'Импорт завершён.',
            'status' => $status,
        ]);
    }
}
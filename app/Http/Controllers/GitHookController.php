<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GitHookController extends Controller
{
    public function handleHook(Request $request)
    {
        $secretKey = env('GIT_HOOK_SECRET_KEY');
        $inputSecretKey = $request->input('secret_key');

        // Проверка секретного ключа
        if ($secretKey !== $inputSecretKey) {
            return response()->json(['message' => 'Invalid secret key.'], 403);
        }

        // Попытка установить блокировку
        $lock = Cache::lock('git-update-lock', 60); // Блокировка на 60 секунд

        if (!$lock->get()) {
            // Если блокировка уже установлена
            return response()->json(['message' => 'Another update process is currently running. Please try again later.'], 429);
        }

        try {
            // 9.1 Логирование даты и IP-адреса
            $ipAddress = $request->ip();
            $currentDate = now()->toDateTimeString();
            Log::info("Git hook triggered", [
                'date' => $currentDate,
                'ip_address' => $ipAddress,
            ]);

            // 9.2 - 9.4 Выполнение Git-операций
            $projectPath = base_path(); // Путь к проекту
            $branchSwitch = $this->executeCommand("git checkout main", $projectPath);
            $resetChanges = $this->executeCommand("git reset --hard", $projectPath);
            $pullChanges = $this->executeCommand("git pull origin main", $projectPath);

            // Логирование выполнения
            Log::info("Git operations completed", [
                'branch_switch' => $branchSwitch,
                'reset_changes' => $resetChanges,
                'pull_changes' => $pullChanges,
            ]);

            return response()->json([
                'message' => 'Project successfully updated from Git.',
                'logs' => [
                    'branch_switch' => $branchSwitch,
                    'reset_changes' => $resetChanges,
                    'pull_changes' => $pullChanges,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error during Git operations", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred during the update process.'], 500);
        } finally {
            // Освобождение блокировки
            $lock->release();
        }
    }

    private function executeCommand(string $command, string $workingDirectory): string
    {
        $output = [];
        $returnVar = 0;
        chdir($workingDirectory);
        exec($command . " 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception(implode("\n", $output));
        }

        return implode("\n", $output);
    }
}
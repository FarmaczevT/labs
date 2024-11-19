<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LogRequest;

class ClearOldLogs extends Command
{
    /**
     * Название команды, чтобы вызывать её в Artisan.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Удаляет старые логи, которым больше 73 часов';

    /**
     * Выполнение команды.
     */
    public function handle()
    {
        // Удаляем записи старше 73 часов
        $deleted = LogRequest::where('created_at', '<', now()->subHours(73))->delete();

        // Выводим результат в консоль
        $this->info("$deleted логов удалено.");
    }
}

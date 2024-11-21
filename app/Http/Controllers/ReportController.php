<?php

namespace App\Http\Controllers;

use App\Models\LogRequest;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function generateReport()
    {
        $timeInterval = env('REPORT_INTERVAL_HOURS', 24);
        $endDate = now();
        $startDate = '2024-11-17 02:12:11';

        // Собираем данные с предварительной загрузкой пользователей
        $logs = LogRequest::with('user') // Загружаем связанные данные пользователей
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Рейтинг вызываемых методов
        $methodRating = $logs->groupBy('controller_method')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Рейтинг редактируемых сущностей (метод PUT)
        $entityRating = $logs
            ->where('http_method', 'PUT')
            ->groupBy(fn($log) => explode('/', $log->url)[5] ?? 'unknown')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Рейтинг пользователей
        $userRating = $logs->groupBy(fn($log) => $log->user->username ?? 'Unknown') // Используем username
            ->map(function ($group) {
                return [
                    'requests' => $group->count(),
                    'changes' => $group->where('http_method', 'PUT')->count(),
                    'successes' => $group->whereIn('response_status', [200, 201])->count(),
                    'failures' => $group->whereNotIn('response_status', [200, 201])->count(),
                ];
            })
            ->toArray();

        // Генерация файла отчета
        $this->createReportFile($methodRating, $entityRating, $userRating, $startDate, $endDate);
    }

    private function createReportFile($methodRating, $entityRating, $userRating, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовок
        $sheet->setCellValue('A1', 'Отчет по активности');
        $sheet->setCellValue('A2', "Временной интервал: $startDate - $endDate");

        // Рейтинг методов
        $sheet->setCellValue('A4', 'Рейтинг вызываемых методов');
        $row = 5;
        foreach ($methodRating as $method => $count) {
            $sheet->setCellValue("A$row", $method);
            $sheet->setCellValue("B$row", $count);
            $row++;
        }

        // Рейтинг редактируемых сущностей
        $sheet->setCellValue("A$row", 'Рейтинг редактируемых сущностей');
        $row++;
        foreach ($entityRating as $entity => $count) {
            $sheet->setCellValue("A$row", $entity);
            $sheet->setCellValue("B$row", $count);
            $row++;
        }

        // Рейтинг пользователей
        $sheet->setCellValue("A$row", 'Рейтинг пользователей');
        $row++;
        $sheet->setCellValue("A$row", 'Пользователь');
        $sheet->setCellValue("B$row", 'Количество запросов');
        $sheet->setCellValue("C$row", 'Количество изменений');
        $sheet->setCellValue("D$row", 'Количество успехов');
        $sheet->setCellValue("E$row", 'Количество не успехов');
        $row++;

        foreach ($userRating as $username => $data) {
            $sheet->setCellValue("A$row", $username);
            $sheet->setCellValue("B$row", $data['requests']);
            $sheet->setCellValue("C$row", $data['changes']);
            $sheet->setCellValue("D$row", $data['successes']);
            $sheet->setCellValue("E$row", $data['failures']);
            $row++;
        }

        // Сохранение файла
        $fileName = "report_" . now()->format('Ymd_His') . ".xlsx";
        $filePath = storage_path("app/reports/$fileName");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Отправка отчета
        $this->sendReportToAdmins($filePath);
    }

    private function sendReportToAdmins($filePath)
    {
        $admins = ['denisgorn123@inbox.ru']; // Замените на получение списка администраторов
        Mail::to($admins)->send(new \App\Mail\ReportMail($filePath));

        // Удаляем файл после отправки
        unlink($filePath);
    }
}
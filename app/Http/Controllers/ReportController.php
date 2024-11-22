<?php

namespace App\Http\Controllers;

use App\Models\LogRequest;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function generateReport()
    {
        // Логирование начала работы задачи
        Log::info('Запуск генерации отчета.', [
            'time' => now()->toDateTimeString()
        ]);

        $timeInterval = env('REPORT_INTERVAL_HOURS', 24);
        $endDate = now();
        // создаем копию текущей даты и отнимаем timeInterval
        $startDate = $endDate->copy()->subHours($timeInterval);

        // Собираем данные с предварительной загрузкой пользователей
        $logs = LogRequest::with('user') // Загружаем связанные данные пользователей
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(); // Извлекаем данные из базы данных и возвращаем коллекцию записей

        // Рейтинг вызываемых методов
        $methodRating = $logs->groupBy('controller_method') // Группируем данные по полю controller_method
            ->map(fn($group) => $group->count()) // считаем количество вызовов
            ->toArray(); // преобразуем данные в массив

        // Рейтинг редактируемых сущностей (метод PUT)
        $entityRating = $logs
            ->where('http_method', 'PUT') // Фильтруем записи по методу PUT
            ->groupBy(fn($log) => explode('/', $log->url)[5] ?? 'unknown') // группируем записи по сущностям
            ->map(fn($group) => $group->count()) // подсчет количества запросов
            ->toArray();

        // Рейтинг пользователей
        $userRating = $logs->groupBy(fn($log) => $log->user->username ?? 'Unknown') // Используем username
            ->map(function ($group) { // для каждого пользователя получаем:
                return [
                    'requests' => $group->count(), // Общее количество запросов
                    'changes' => $group->where('http_method', 'PUT')->count(), // Количество запросов с методом PUT
                    'successes' => $group->whereIn('response_status', [200, 201])->count(), // Количество успешных запросов
                    'failures' => $group->whereNotIn('response_status', [200, 201])->count(), //  Количество неуспешных запросов
                ];
            })
            ->toArray();

        // Генерация файла отчета
        $this->createReportFile($methodRating, $entityRating, $userRating, $startDate, $endDate);

        // Логирование успешного завершения задачи
        Log::info('Отчет успешно сгенерирован.', [
            'time' => now()->toDateTimeString()
        ]);

        return response()->json(['message' => 'The activity report has been sent successfully'], 200);
    }

    private function createReportFile($methodRating, $entityRating, $userRating, $startDate, $endDate)
    {   // Создаем объект Excel
        $spreadsheet = new Spreadsheet();
        // Получаем активный рабочий лист для добавления данных
        $sheet = $spreadsheet->getActiveSheet();

        // Установка стилей
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], // жирный черный шрифт
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],// Заливка фона
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER], // Выравнивание по центру
        ];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2c405a']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $borderStyle = [ // Стили рамок
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Заголовок отчета
        $sheet->setCellValue('A1', 'Отчет по активности')->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A2', "Временной интервал: $startDate - $endDate")->mergeCells('A2:E2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 4; // Начальная строка для заполнения данными

        // Рейтинг вызываемых методов
        $sheet->setCellValue("A$row", 'Рейтинг вызываемых методов')->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row:B$row")->applyFromArray($titleStyle);
        $row++;
        $sheet->setCellValue("A$row", 'Метод')->setCellValue("B$row", 'Количество вызовов');
        $sheet->getStyle("A$row:B$row")->applyFromArray($headerStyle);
        $row++;
        foreach ($methodRating as $method => $count) {
            $sheet->setCellValue("A$row", $method); // название метода
            $sheet->setCellValue("B$row", $count); // количество вызовов
            $row++;
        }
        $sheet->getStyle("A5:B" . ($row - 1))->applyFromArray($borderStyle); // из за инкремента в цикле используем B$row - 1

        // Рейтинг редактируемых сущностей
        $row++;
        $sheet->setCellValue("A$row", 'Рейтинг редактируемых сущностей')->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row:B$row")->applyFromArray($titleStyle);
        $row++;
        $sheet->setCellValue("A$row", 'Сущность')->setCellValue("B$row", 'Количество изменений');
        $sheet->getStyle("A$row:B$row")->applyFromArray($headerStyle);
        $row++;
        foreach ($entityRating as $entity => $count) {
            $sheet->setCellValue("A$row", $entity);
            $sheet->setCellValue("B$row", $count);
            $row++;
        }
        $sheet->getStyle("A" . ($row - count($entityRating) - 1) . ":B" . ($row - 1))->applyFromArray($borderStyle);

        // Рейтинг пользователей
        $row++;
        $sheet->setCellValue("A$row", 'Рейтинг пользователей')->mergeCells("A$row:E$row");
        $sheet->getStyle("A$row:E$row")->applyFromArray($titleStyle);
        $row++;
        $sheet->setCellValue("A$row", 'Пользователь')->setCellValue("B$row", 'Количество запросов')
            ->setCellValue("C$row", 'Количество изменений')->setCellValue("D$row", 'Количество успешных запросов')
            ->setCellValue("E$row", 'Количество неуспешных запросов');
        $sheet->getStyle("A$row:E$row")->applyFromArray($headerStyle);
        $row++;
        foreach ($userRating as $username => $data) {
            $sheet->setCellValue("A$row", $username);
            $sheet->setCellValue("B$row", $data['requests']);
            $sheet->setCellValue("C$row", $data['changes']);
            $sheet->setCellValue("D$row", $data['successes']);
            $sheet->setCellValue("E$row", $data['failures']);
            $row++;
        }
        $sheet->getStyle("A" . ($row - count($userRating) - 1) . ":E" . ($row - 1))->applyFromArray($borderStyle);

        // Автоматическая ширина колонок
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Сохранение файла
        $fileName = "report_" . now()->format('Ymd_His') . ".xlsx";
        $filePath = storage_path("app/reports/$fileName");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $writer = new Xlsx($spreadsheet); // Создаем объект класса Xlsx и передаем объект $spreadsheet, который представляет созданную таблицу Excel
        $writer->save($filePath);

        // Отправка отчета
        $this->sendReportToAdmins($filePath, $fileName);
    }

    private function sendReportToAdmins($filePath, $fileName)
    {
        $admins = ['denisgorn123@inbox.ru'];
        Mail::to($admins)->send(new \App\Mail\ReportMail($filePath, $fileName));

        // Удаляем файл после отправки
        unlink($filePath);
    }
}
<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\User;
use App\Models\Permission;

class ExportController extends Controller
{
    public function exportUsers()
    {
        $users = User::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

         // Установка стилей
         $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], // жирный черный шрифт
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],// Заливка фона
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER], // Выравнивание по центру
        ];
        $borderStyle = [ // Стили рамок
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Заголовки столбцов
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'username');
        $sheet->setCellValue('C1', 'email');
        $sheet->setCellValue('D1', 'password');
        $sheet->setCellValue('E1', 'birthday');
        $sheet->setCellValue('F1', 'photo_id');
        $sheet->setCellValue('G1', 'tfa_token');
        $sheet->getStyle("A1:G1")->applyFromArray($titleStyle);
        
        // Данные пользователей
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user->id);
            $sheet->setCellValue("B{$row}", $user->username);
            $sheet->setCellValue("C{$row}", $user->email);
            $sheet->setCellValue("D{$row}", $user->password);
            $sheet->setCellValue("E{$row}", $user->birthday);
            $sheet->setCellValue("F{$row}", $user->photo_id);
            $sheet->setCellValue("G{$row}", $user->tfa_token);
            $row++;
        }
        $sheet->getStyle("A2:G" . ($row - 1))->applyFromArray($borderStyle);

        // Автоматическая ширина колонок
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Создание файла Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = 'users.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPermissions()
    {
        $users = Permission::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

         // Установка стилей
         $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], // жирный черный шрифт
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],// Заливка фона
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER], // Выравнивание по центру
        ];
        $borderStyle = [ // Стили рамок
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Заголовки столбцов
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'description');
        $sheet->setCellValue('D1', 'code');
        $sheet->setCellValue('E1', 'created_by');
        $sheet->getStyle("A1:E1")->applyFromArray($titleStyle);
        
        // Данные пользователей
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user->id);
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", $user->description);
            $sheet->setCellValue("D{$row}", $user->code);
            $sheet->setCellValue("E{$row}", $user->created_by);
            $row++;
        }
        $sheet->getStyle("A2:E" . ($row - 1))->applyFromArray($borderStyle);

        // Автоматическая ширина колонок
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Создание файла Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = 'permissions.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\User;
use ZipArchive;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $maxFileSize = env('MAX_FILE_SIZE', 5120);

        $request->validate([
            'file' => "required|file|max:$maxFileSize",
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $user = $request->user();

        // Генерация уникального имени файла
        $timestamp = now()->timestamp;
        $uniqueName = "{$user->id}_{$timestamp}." . $file->getClientOriginalExtension();

        // Сохранение файла
        $path = $file->storeAs('files', $uniqueName, 'private');

        // Сохранение записи в БД
        $fileModel = File::create([
            'filename' => $file->getClientOriginalName(),
            'description' => $request->input('description'),
            'format' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        // Обновление photo_id у пользователя
        $user->update(['photo_id' => $fileModel->id]);

        // Создание аватара
        $this->createAvatar($path, $user->id, $timestamp);

        return response()->json([
            'message' => 'Файл успешно загружен',
            'file' => $fileModel,
        ]);
    }


    private function createAvatar(string $path, int $userId, int $timestamp)
    {
        $originalPath = storage_path('app/private/' . $path);
        $avatarPath = storage_path("app/public/avatars/{$userId}_{$timestamp}_avatar." . pathinfo($path, PATHINFO_EXTENSION));

        if (!file_exists(dirname($avatarPath))) {
            mkdir(dirname($avatarPath), 0755, true);
        }

        Image::read($originalPath)
            ->resize(128, 128)
            ->save($avatarPath);
    }


    public function downloadAvatar(int $userId)
    {
        // Находим пользователя по ID
        $user = User::findOrFail($userId);

        // Проверяем, указано ли значение в photo_id
        if (!$user->photo_id) {
            abort(404, 'Оригинальное изображение не задано.');
        }

        // Получаем запись файла из таблицы files по photo_id
        $file = File::find($user->photo_id);

        // Проверяем, существует ли файл
        if (!$file || !Storage::exists($file->path)) {
            abort(404, 'Файл не найден.');
        }

        // Возвращаем файл для скачивания
        return Storage::download($file->path, $user->username . '_original.' . $file->format);
    }


    public function delete(File $file)
    {
        // Удаление файла с диска
        Storage::disk('public')->delete($file->path);

        // Мягкое удаление записи
        $file->delete();

        return response()->json(['message' => 'Файл успешно удален']);
    }

    public function list()
    {
        $files = File::all();

        return response()->json(['files' => $files]);
    }

    public function downloadArchive()
    {
        try {
            // выбираются все пользователи, у которых указано поле photo_id
            $users = User::whereNotNull('photo_id')->with('photo')->get();
            // Путь к архиву
            $zipFileName = storage_path('app/public/photo_archive.zip');

            // Удаляем существующий архив, если он уже есть
            if (file_exists($zipFileName)) {
                unlink($zipFileName);
            }
            // Создаем объект зип архив
            $zip = new ZipArchive();
            // создаем или перезаписываем архив
            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                abort(500, 'Не удалось создать ZIP архив.');
            }

            foreach ($users as $user) {
                $file = $user->photo;

                if (!$file || !Storage::exists($file->path)) {
                    continue;
                }

                // Извлекаем имя оригинального файла без расширения
                $originalFileName = pathinfo($file->path, PATHINFO_FILENAME);  // Имя файла без расширения

                // Формируем пути для оригинала и аватара
                $originalName = "{$user->username}_{$originalFileName}." . $file->format;
                $avatarName = "{$user->username}_{$originalFileName}_avatar." . $file->format;

                $originalPath = storage_path('app/private/' . $file->path); // Оригинальный файл
                $avatarPath = storage_path("app/public/avatars/{$originalFileName}_avatar." . $file->format);  // Путь к аватару

                // Добавление файлов в архив
                if (file_exists($originalPath)) {
                    $zip->addFile($originalPath, "originals/{$originalName}");
                }

                if (file_exists($avatarPath)) {
                    $zip->addFile($avatarPath, "avatars/{$avatarName}");
                }
            }

            $zip->close();

            if (!file_exists($zipFileName)) {
                abort(500, 'Ошибка создания ZIP архива.');
            }

            return response()->download($zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            abort(500, 'Произошла ошибка при создании архива.');
        }
    }
}
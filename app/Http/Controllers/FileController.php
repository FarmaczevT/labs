<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $maxFileSize = env('MAX_FILE_SIZE', 5120); // В байтах, по умолчанию 5 МБ

        $request->validate([
            'file' => "required|file|max:$maxFileSize", // Проверка размера файла
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');

        // Генерация уникального имени
        $uniqueName = uniqid() . '.' . $file->getClientOriginalExtension();

        // Сохранение файла
        $path = $file->storeAs('files', $uniqueName, 'private');

        // Создание записи в базе данных
        $fileModel = File::create([
            'filename' => $file->getClientOriginalName(),
            'description' => $request->input('description'),
            'format' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        $user = $request->user();

        $user->update(['photo_id' => $fileModel->id]);

        // Создание аватара
        $this->createAvatar($path);

        return response()->json([
            'message' => 'Файл успешно загружен',
            'file' => $fileModel,
        ]);
    }

    private function createAvatar(string $path)
    {
        // Оригинальный путь файла
        $fullPath = storage_path('app/private/' . $path);

        // Путь для сохранения аватара
        $avatarPath = storage_path('app/public/avatars/' . basename($path));

        // Создание директории, если она не существует
        if (!file_exists(dirname($avatarPath))) {
            mkdir(dirname($avatarPath), 0755, true);
        }

        // Создание и сохранение аватара
        Image::read($fullPath)
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

        // Получаем запись файла из таблицы files
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
}
<?php

namespace App\Traits;

use App\Models\ChangeLog;

trait LogsChanges
{
    public static function bootLogsChanges()
    {
        //static:: указывает, что метод будет вызван в контексте класса, который использует трейт
        // Логирование после создания модели
        static::created(function ($model) {
            static::logChange($model, 'create', [], $model->attributesToArray());
        });

        // Логирование перед обновлением модели
        static::updating(function ($model) {
            $before = $model->getOriginal();
            $after = $model->getDirty();
            static::logChange($model, 'update', $before, $after);
        });

        // Логирование перед удалением модели
        static::deleting(function ($model) {
            static::logChange($model, 'delete', $model->attributesToArray(), []);
        });
    }

    private static function logChange($model, $action, $before, $after)
    {
        // Получение ID текущего пользователя из JWT
        $userId = request()->user()->id;

        ChangeLog::create([
            'entity_name' => $model->getTable(), // Имя сущности
            'entity_id' => $model->id, // ID сущности
            'before' => json_encode($before),
            'after' => json_encode($after),
            'created_by' => $userId, // Пользователь, выполнивший изменение
        ]);
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Наименование файла
            $table->text('description')->nullable(); // Описание файла
            $table->string('format'); // Формат файла (например, jpg, png, pdf)
            $table->unsignedBigInteger('size'); // Размер файла в байтах
            $table->string('path'); // Ссылка к файлу на сервере
            $table->timestamps(); // Служебные поля created_at, updated_at
            $table->softDeletes(); // Служебное поле deleted_at для мягкого удаления
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
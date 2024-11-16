<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeLogsTable extends Migration
{
    public function up()
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name')->comment('Имя сущности'); // Имя сущности (например, 'roles', 'permissions')
            $table->unsignedBigInteger('entity_id'); // ID записи в сущности
            $table->jsonb('before')->comment('Значение до изменения');
            $table->jsonb('after')->comment('Значение после изменения');

            $table->timestamp('created_at')->useCurrent()->comment('Время создания записи');
            $table->foreignId('created_by')->references('id')->on('users'); // ID пользователя, который сделал изменение

        });
    }

    public function down()
    {
        Schema::dropIfExists('change_logs');
    }
}
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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');

            // Служебные поля
            $table->timestamp('created_at')->useCurrent()->comment('Время создания записи');
            $table->integer('created_by')->comment('Идентификатор пользователя создавшего запись');
            $table->softDeletes('deleted_at')->comment('Время мягкого удаления записи');
            $table->integer('deleted_by')->nullable()->comment('Идентификатор пользователя удалившего запись');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};

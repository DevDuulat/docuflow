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
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                // Привязки к структуре
                $table->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                // Содержимое
                $table->longText('content')->nullable(); // Текстовая версия
                // Типизация и метаданные
                $table->string('document_number')->nullable()->index(); // Регистрационный номер (важно для СЭД)
                $table->text('comment')->nullable();
                // Статусы
                $table->string('status')->default('draft')->index(); // Проект, Опубликован, Архив
                // Авторство
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

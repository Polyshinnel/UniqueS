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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('title'); // Название объявления
            $table->unsignedBigInteger('category_id'); // Категория
            $table->text('main_characteristics')->nullable(); // Основные характеристики
            $table->text('complectation')->nullable(); // Комплектация
            $table->text('technical_characteristics')->nullable(); // Технические характеристики
            $table->text('additional_info')->nullable(); // Дополнительная информация
            
            // Данные этапов из товара (копируются и могут редактироваться)
            $table->json('check_data')->nullable(); // Данные проверки (статус, комментарий)
            $table->json('loading_data')->nullable(); // Данные погрузки (тип, комментарий)
            $table->json('removal_data')->nullable(); // Данные демонтажа (тип, комментарий)
            
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by'); // Создатель объявления
            $table->timestamp('published_at')->nullable(); // Дата публикации
            $table->timestamps();

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('product_categories');
            $table->foreign('created_by')->references('id')->on('users');
            
            // Индексы
            $table->index(['product_id', 'status']);
            $table->index('category_id');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};

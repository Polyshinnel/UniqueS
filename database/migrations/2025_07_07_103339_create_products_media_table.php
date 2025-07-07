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
        Schema::create('products_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('file_name'); // Оригинальное имя файла
            $table->string('file_path'); // Путь к файлу
            $table->string('file_type'); // image или video
            $table->string('mime_type'); // MIME тип файла
            $table->bigInteger('file_size'); // Размер файла в байтах
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index(['product_id', 'sort_order']);
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_media');
    }
};

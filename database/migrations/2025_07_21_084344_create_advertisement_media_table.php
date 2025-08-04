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
        Schema::create('advertisement_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
            $table->foreignId('product_media_id')->nullable()->constrained('products_media')->onDelete('set null'); // Ссылка на медиафайл товара
            $table->string('file_name'); // Оригинальное имя файла
            $table->string('file_path'); // Путь к файлу
            $table->string('file_type'); // image или video
            $table->string('mime_type'); // MIME тип файла
            $table->bigInteger('file_size'); // Размер файла в байтах
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->boolean('is_selected_from_product')->default(false); // Выбран ли из медиафайлов товара
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index(['advertisement_id', 'sort_order']);
            $table->index('file_type');
            $table->index('product_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_media');
    }
};

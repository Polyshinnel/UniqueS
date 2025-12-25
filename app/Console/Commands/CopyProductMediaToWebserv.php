<?php

namespace App\Console\Commands;

use App\Models\ProductMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyProductMediaToWebserv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:copy-media-to-webserv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Копирует медиафайлы из таблицы products_media в папку webserv/products с сохранением структуры каталогов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начало копирования медиафайлов в webserv/products...');
        $this->newLine();

        // Получаем все медиафайлы, которые еще не скопированы
        $mediaFiles = ProductMedia::where(function ($query) {
            $query->where('copied', false)
                  ->orWhereNull('copied');
        })->get();

        if ($mediaFiles->isEmpty()) {
            $this->info('Медиафайлы для копирования не найдены.');
            return 0;
        }

        $this->info("Найдено медиафайлов для обработки: {$mediaFiles->count()}");
        $this->newLine();

        // Определяем пути
        // webserv находится на том же уровне что и products в storage/app/public
        $sourceBasePath = storage_path('app' . DIRECTORY_SEPARATOR . 'public');
        $targetBasePath = $sourceBasePath . DIRECTORY_SEPARATOR . 'webserv' . DIRECTORY_SEPARATOR . 'products';

        $this->info("Исходная папка: {$sourceBasePath}");
        $this->info("Целевая папка: {$targetBasePath}");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $existsCount = 0;

        foreach ($mediaFiles as $media) {
            try {
                // Получаем исходный путь к файлу
                $sourceFilePath = $sourceBasePath . DIRECTORY_SEPARATOR . $media->file_path;
                
                // Проверяем существование исходного файла
                if (!file_exists($sourceFilePath)) {
                    $this->warn("  ⚠ Пропущено ID: {$media->id} - исходный файл не найден: {$sourceFilePath}");
                    $skippedCount++;
                    continue;
                }

                // Формируем целевой путь, сохраняя структуру каталогов
                // Если file_path начинается с "webserv/products/", убираем этот префикс
                // Если file_path начинается с "products/", убираем префикс "products/"
                // Иначе используем file_path как есть
                $targetRelativePath = $media->file_path;
                
                if (strpos($targetRelativePath, 'webserv/products/') === 0) {
                    // Убираем префикс webserv/products/
                    $targetRelativePath = substr($targetRelativePath, strlen('webserv/products/'));
                } elseif (strpos($targetRelativePath, 'products/') === 0) {
                    // Убираем префикс products/
                    $targetRelativePath = substr($targetRelativePath, strlen('products/'));
                }
                
                // Формируем полный целевой путь
                $targetFilePath = $targetBasePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $targetRelativePath);
                $targetDir = dirname($targetFilePath);

                // Проверяем, существует ли файл в целевой папке
                if (file_exists($targetFilePath)) {
                    $this->info("  ⊙ Пропущено ID: {$media->id} - файл уже существует: {$targetRelativePath}");
                    $existsCount++;
                    
                    // Помечаем как скопированный, даже если пропустили
                    $media->copied = true;
                    $media->save();
                    continue;
                }

                // Создаем директорию, если она не существует
                if (!is_dir($targetDir)) {
                    if (!File::makeDirectory($targetDir, 0755, true)) {
                        $this->error("  ✗ Ошибка создания директории ID: {$media->id} - {$targetDir}");
                        $errorCount++;
                        continue;
                    }
                }

                // Копируем файл
                if (!copy($sourceFilePath, $targetFilePath)) {
                    $this->error("  ✗ Ошибка копирования файла ID: {$media->id} - {$targetRelativePath}");
                    $errorCount++;
                    continue;
                }

                // Обновляем флаг copied
                $media->copied = true;
                $media->save();

                $this->info("  ✓ Скопировано ID: {$media->id} - {$targetRelativePath}");
                $successCount++;

            } catch (\Exception $e) {
                $this->error("  ✗ Ошибка при обработке медиафайла ID {$media->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        // Итоговая статистика
        $this->newLine();
        $this->info('=== Результаты обработки ===');
        $this->info("Всего найдено: {$mediaFiles->count()}");
        $this->info("Успешно скопировано: {$successCount}");
        
        if ($existsCount > 0) {
            $this->info("Пропущено (уже существует): {$existsCount}");
        }
        
        if ($skippedCount > 0) {
            $this->warn("Пропущено (файл не найден): {$skippedCount}");
        }
        
        if ($errorCount > 0) {
            $this->error("Ошибок: {$errorCount}");
        }

        $this->newLine();
        $this->info('Копирование завершено.');

        return 0;
    }
}


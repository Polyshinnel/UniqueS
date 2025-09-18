<?php

namespace App\Console\Commands;

use App\Models\ProductMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ResizeProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:resize-products {--dry-run : Показать что будет изменено без выполнения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Изменяет размер изображений товаров согласно требованиям';

    /**
     * Максимальные размеры изображений
     * Для альбомной ориентации: 1920x1080
     * Для портретной ориентации: 1080x1920
     */
    private const MAX_LANDSCAPE_WIDTH = 1920;
    private const MAX_LANDSCAPE_HEIGHT = 1080;
    private const MAX_PORTRAIT_WIDTH = 1080;
    private const MAX_PORTRAIT_HEIGHT = 1920;
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Режим предварительного просмотра - изменения не будут применены');
        }

        // Получаем изображения для обработки
        $images = ProductMedia::where('file_type', 'image')
            ->where('achieved', false)
            ->get();

        if ($images->isEmpty()) {
            $this->info('Изображения для обработки не найдены.');
            return 0;
        }

        $this->info("Найдено изображений для обработки: {$images->count()}");
        
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($images->count());
        $progressBar->start();

        foreach ($images as $image) {
            try {
                $result = $this->processImage($image, $isDryRun);
                
                if ($result === 'processed') {
                    $processedCount++;
                } elseif ($result === 'skipped') {
                    $skippedCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("Ошибка при обработке изображения ID {$image->id}: " . $e->getMessage());
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Выводим результаты
        $this->info("Результаты обработки:");
        $this->info("- Обработано: {$processedCount}");
        $this->info("- Пропущено: {$skippedCount}");
        $this->info("- Ошибок: {$errorCount}");

        return 0;
    }

    /**
     * Обрабатывает одно изображение
     */
    private function processImage(ProductMedia $image, bool $isDryRun): string
    {
        $filePath = storage_path('app/public/' . $image->file_path);
        
        if (!file_exists($filePath)) {
            $this->warn("Файл не найден: {$image->file_path}");
            return 'error';
        }

        // Получаем размеры изображения
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            $this->warn("Не удалось получить информацию об изображении: {$image->file_path}");
            return 'error';
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        
        // Определяем ориентацию изображения
        $isLandscape = $originalWidth > $originalHeight;
        
        // Вычисляем новые размеры
        $newDimensions = $this->calculateNewDimensions($originalWidth, $originalHeight, $isLandscape);
        
        // Проверяем, нужно ли изменять размер
        if ($newDimensions['width'] >= $originalWidth && $newDimensions['height'] >= $originalHeight) {
            if (!$isDryRun) {
                $this->markAsArchived($image);
            }
            return 'skipped';
        }

        if ($isDryRun) {
            $this->line("Будет изменено: {$image->file_name} ({$originalWidth}x{$originalHeight} -> {$newDimensions['width']}x{$newDimensions['height']})");
            return 'processed';
        }

        // Изменяем размер изображения
        try {
            $this->resizeImage($filePath, $newDimensions['width'], $newDimensions['height']);
            $this->markAsArchived($image);
            return 'processed';
        } catch (\Exception $e) {
            $this->error("Ошибка при изменении размера {$image->file_name}: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Вычисляет новые размеры изображения
     */
    private function calculateNewDimensions(int $width, int $height, bool $isLandscape): array
    {
        if ($isLandscape) {
            // Альбомная ориентация: максимальные размеры 1920x1080
            if ($width <= self::MAX_LANDSCAPE_WIDTH && $height <= self::MAX_LANDSCAPE_HEIGHT) {
                return ['width' => $width, 'height' => $height];
            }
            
            $ratio = min(self::MAX_LANDSCAPE_WIDTH / $width, self::MAX_LANDSCAPE_HEIGHT / $height);
        } else {
            // Портретная ориентация: максимальные размеры 1080x1920
            if ($width <= self::MAX_PORTRAIT_WIDTH && $height <= self::MAX_PORTRAIT_HEIGHT) {
                return ['width' => $width, 'height' => $height];
            }
            
            $ratio = min(self::MAX_PORTRAIT_WIDTH / $width, self::MAX_PORTRAIT_HEIGHT / $height);
        }

        return [
            'width' => (int) round($width * $ratio),
            'height' => (int) round($height * $ratio)
        ];
    }

    /**
     * Изменяет размер изображения
     */
    private function resizeImage(string $filePath, int $newWidth, int $newHeight): void
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($filePath);
        $image->scaleDown($newWidth, $newHeight);
        $image->save($filePath, 90); // Сохраняем с качеством 90%
    }

    /**
     * Помечает изображение как обработанное
     */
    private function markAsArchived(ProductMedia $image): void
    {
        $image->update(['achieved' => true]);
    }
}

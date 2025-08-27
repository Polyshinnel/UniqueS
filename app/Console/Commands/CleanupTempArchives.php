<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CleanupTempArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:temp-archives {--hours=24 : Удалить архивы старше указанного количества часов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очищает временные ZIP архивы медиафайлов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tempDir = storage_path('app/temp');
        $hours = $this->option('hours');
        $cutoffTime = now()->subHours($hours);
        
        if (!File::exists($tempDir)) {
            $this->info('Временная директория не существует.');
            return 0;
        }
        
        $files = File::files($tempDir);
        $deletedCount = 0;
        $totalSize = 0;
        
        foreach ($files as $file) {
            $fileTime = \Carbon\Carbon::createFromTimestamp($file->getMTime());
            
            if ($fileTime->lt($cutoffTime)) {
                $fileSize = $file->getSize();
                File::delete($file->getPathname());
                $deletedCount++;
                $totalSize += $fileSize;
                
                $this->line("Удален файл: {$file->getFilename()} (размер: " . $this->formatBytes($fileSize) . ")");
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("Удалено файлов: {$deletedCount}");
            $this->info("Освобождено места: " . $this->formatBytes($totalSize));
        } else {
            $this->info("Файлы для удаления не найдены.");
        }
        
        return 0;
    }
    
    /**
     * Форматирует размер файла в читаемый вид
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

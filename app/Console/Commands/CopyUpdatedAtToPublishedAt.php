<?php

namespace App\Console\Commands;

use App\Models\Advertisement;
use Illuminate\Console\Command;

class CopyUpdatedAtToPublishedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:copy-updated-to-published';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Копирует дату и время updated_at в published_at для всех объявлений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начало копирования updated_at в published_at для всех объявлений...');
        $this->newLine();

        // Получаем все объявления
        $advertisements = Advertisement::all();

        if ($advertisements->isEmpty()) {
            $this->info('Объявления не найдены.');
            return 0;
        }

        $this->info("Найдено объявлений для обработки: {$advertisements->count()}");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($advertisements as $advertisement) {
            try {
                // Проверяем, есть ли updated_at
                if (!$advertisement->updated_at) {
                    $this->warn("  Пропущено объявление ID: {$advertisement->id} - нет updated_at");
                    $skippedCount++;
                    continue;
                }

                // Копируем updated_at в published_at
                $advertisement->published_at = $advertisement->updated_at;
                
                // Сохраняем без обновления updated_at
                $advertisement->timestamps = false;
                $advertisement->save();
                $advertisement->timestamps = true;

                $this->info("  ✓ Объявление ID: {$advertisement->id} - скопировано {$advertisement->updated_at} → {$advertisement->published_at}");
                $successCount++;

            } catch (\Exception $e) {
                $this->error("  ✗ Ошибка при обработке объявления ID {$advertisement->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        // Итоговая статистика
        $this->newLine();
        $this->info('=== Результаты обработки ===');
        $this->info("Всего найдено: {$advertisements->count()}");
        $this->info("Успешно обработано: {$successCount}");
        
        if ($skippedCount > 0) {
            $this->warn("Пропущено (нет updated_at): {$skippedCount}");
        }
        
        if ($errorCount > 0) {
            $this->error("Ошибок: {$errorCount}");
        }

        $this->newLine();
        $this->info('Копирование завершено.');

        return 0;
    }
}


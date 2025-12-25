<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Выполнение команды изменения размера изображений товаров каждые 20 минут
        $schedule->command('images:resize-products')->everyTwoMinutes();

        // Проверка объявлений в резерве и автоматический перевод в ревизию через 7 дней
        $schedule->command('advertisements:check-reserved')->daily();

        // Проверка объявлений в продаже и автоматический перевод в ревизию через 30 дней
        $schedule->command('advertisements:check-active')->daily();
        $schedule->command('app:check-hold-adv')->daily();
        $schedule->command('advertisements:export-xml')->everyFiveMinutes();

        // Копирование медиафайлов товаров в webserv/products каждый день в 00:00
        $schedule->command('products:copy-media-to-webserv')->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestProductLog extends Command
{
    protected $signature = 'test:product-log {product_id}';
    protected $description = 'Тестирует функциональность логов товаров';

    public function handle()
    {
        $productId = $this->argument('product_id');
        
        // Проверяем существование товара
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Товар с ID {$productId} не найден");
            return 1;
        }

        $this->info("Товар: {$product->name}");

        // Проверяем типы логов
        $logTypes = LogType::all();
        $this->info("Доступные типы логов:");
        foreach ($logTypes as $type) {
            $this->line("  - {$type->name} (цвет: {$type->color})");
        }

        // Получаем последний лог
        $lastLog = ProductLog::where('product_id', $productId)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        if ($lastLog) {
            $this->info("\nПоследний лог:");
            $this->line("  Тип: " . ($lastLog->type ? $lastLog->type->name : 'Неизвестный тип'));
            $this->line("  Цвет: " . ($lastLog->type ? $lastLog->type->color : '#133E71'));
            $this->line("  Дата: " . $lastLog->created_at->format('d.m.Y H:i:s'));
            $this->line("  Сообщение: " . $lastLog->log);
            $this->line("  Создал: " . ($lastLog->user_id ? ($lastLog->user ? $lastLog->user->name : 'Пользователь не найден') : 'Система'));
        } else {
            $this->warn("Логи для товара не найдены");
        }

        // Получаем все логи товара
        $allLogs = ProductLog::where('product_id', $productId)
            ->with(['type', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($allLogs->count() > 0) {
            $this->info("\nВсе логи товара ({$allLogs->count()}):");
            foreach ($allLogs as $log) {
                $this->line("  - " . $log->created_at->format('d.m.Y H:i:s') . " | " . 
                    ($log->type ? $log->type->name : 'Неизвестный тип') . " | " . 
                    ($log->user ? $log->user->name : 'Система') . " | " . 
                    Str::limit($log->log, 50));
            }
        }

        return 0;
    }
}

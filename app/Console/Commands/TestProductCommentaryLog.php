<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductLog;
use App\Models\LogType;
use Illuminate\Console\Command;

class TestProductCommentaryLog extends Command
{
    protected $signature = 'test:product-commentary-log {product_id}';
    protected $description = 'Тестирует логирование изменений общего комментария после осмотра товара';

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
        $this->line("Текущий комментарий: " . ($product->common_commentary_after ?: 'не указан'));

        // Проверяем типы логов
        $systemLogType = LogType::where('name', 'Системный')->first();
        if (!$systemLogType) {
            $this->error("Тип лога 'Системный' не найден");
            return 1;
        }

        $this->info("Тип лога 'Системный' найден (ID: {$systemLogType->id})");

        // Получаем последние логи товара
        $recentLogs = ProductLog::where('product_id', $productId)
            ->with(['type'])
            ->latest()
            ->limit(5)
            ->get();

        if ($recentLogs->isNotEmpty()) {
            $this->info("\nПоследние логи товара:");
            foreach ($recentLogs as $log) {
                $this->line("  [" . $log->created_at->format('d.m.Y H:i:s') . "] " . 
                           ($log->type ? $log->type->name : 'Неизвестный тип') . ": " . $log->log);
            }
        } else {
            $this->warn("Логи для товара не найдены");
        }

        // Симулируем изменение комментария
        $this->info("\nСимулируем изменение комментария...");
        
        $oldCommentary = $product->common_commentary_after;
        $newCommentary = "Тестовый комментарий от " . now()->format('d.m.Y H:i:s');
        
        // Создаем лог вручную для демонстрации
        $oldText = $oldCommentary ?: 'пустой комментарий';
        $newText = $newCommentary ?: 'пустой комментарий';
        
        $logMessage = "Изменен Общий комментарий после осмотра, с \"{$oldText}\" на \"{$newText}\"";
        
        $log = ProductLog::create([
            'product_id' => $product->id,
            'type_id' => $systemLogType->id,
            'log' => $logMessage,
            'user_id' => null // От имени системы
        ]);

        $this->info("Создан тестовый лог:");
        $this->line("  ID: {$log->id}");
        $this->line("  Сообщение: {$log->log}");
        $this->line("  Тип: " . ($log->type ? $log->type->name : 'Неизвестный тип'));
        $this->line("  Дата: " . $log->created_at->format('d.m.Y H:i:s'));

        $this->info("\nТестирование завершено успешно!");
        return 0;
    }
} 
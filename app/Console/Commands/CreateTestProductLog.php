<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestProductLog extends Command
{
    protected $signature = 'create:test-product-log {product_id} {--type=1} {--user=null} {--message="Тестовый лог товара"}';
    protected $description = 'Создает тестовый лог для товара';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $typeId = $this->option('type');
        $userId = $this->option('user');
        $message = $this->option('message');
        
        // Проверяем существование товара
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Товар с ID {$productId} не найден");
            return 1;
        }

        // Проверяем существование типа лога
        $logType = LogType::find($typeId);
        if (!$logType) {
            $this->error("Тип лога с ID {$typeId} не найден");
            return 1;
        }

        // Проверяем существование пользователя (если указан)
        if ($userId && $userId !== 'null') {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }
        }

        // Создаем лог
        $log = ProductLog::create([
            'product_id' => $productId,
            'user_id' => ($userId && $userId !== 'null') ? $userId : null,
            'type_id' => $typeId,
            'log' => $message
        ]);

        $this->info("Лог успешно создан:");
        $this->line("  ID: {$log->id}");
        $this->line("  Товар: {$product->name}");
        $this->line("  Тип: " . ($logType->name));
        $this->line("  Сообщение: {$message}");
        $this->line("  Пользователь: " . (($userId && $userId !== 'null') ? $user->name : 'Система'));
        $this->line("  Дата: " . $log->created_at->format('d.m.Y H:i:s'));

        return 0;
    }
}

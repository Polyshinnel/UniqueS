<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductAction;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestProductAction extends Command
{
    protected $signature = 'create:test-product-action {product_id} {--user=1} {--action="Проверить статус товара"} {--expired_at=+7 days}';
    protected $description = 'Создает тестовое действие для товара';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $userId = $this->option('user');
        $action = $this->option('action');
        $expiredAt = $this->option('expired_at');
        
        // Проверяем существование товара
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Товар с ID {$productId} не найден");
            return 1;
        }

        // Проверяем существование пользователя
        $user = User::find($userId);
        if (!$user) {
            $this->error("Пользователь с ID {$userId} не найден");
            return 1;
        }

        // Парсим дату истечения
        $expiredDate = now()->modify($expiredAt);

        // Создаем действие
        $productAction = ProductAction::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'action' => $action,
            'expired_at' => $expiredDate,
            'status' => false,
        ]);

        $this->info("Действие успешно создано:");
        $this->line("  ID: {$productAction->id}");
        $this->line("  Товар: {$product->name}");
        $this->line("  Действие: {$action}");
        $this->line("  Пользователь: {$user->name}");
        $this->line("  Дата истечения: " . $expiredDate->format('d.m.Y H:i:s'));
        $this->line("  Статус: " . ($productAction->status ? 'Выполнено' : 'Не выполнено'));

        return 0;
    }
}

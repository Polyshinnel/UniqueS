<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ListProducts extends Command
{
    protected $signature = 'list:products';
    protected $description = 'Выводит список всех товаров';

    public function handle()
    {
        $products = Product::all(['id', 'name', 'sku']);
        
        if ($products->isEmpty()) {
            $this->info('Товары не найдены');
            return 0;
        }
        
        $this->info('Список товаров:');
        $this->table(['ID', 'SKU', 'Название'], $products->map(function($product) {
            return [$product->id, $product->sku, $product->name];
        }));
        
        return 0;
    }
}

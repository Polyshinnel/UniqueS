<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\Advertisement;
use App\Models\AdvertisementStatus;
use App\Models\ProductAction;
use App\Models\AdvAction;
use App\Models\ProductLog;
use App\Models\AdvLog;

class TestProductStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:product-status {product_id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует логику перевода товара в статус Холд или Отказ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->argument('product_id');
        $status = $this->argument('status');
        
        if (!in_array($status, ['Холд', 'Отказ'])) {
            $this->error("Статус должен быть 'Холд' или 'Отказ'!");
            return 1;
        }
        
        $this->info("Тестирование логики перевода товара ID {$productId} в статус {$status}...");
        
        // Находим товар
        $product = Product::with(['advertisements.status', 'actions'])->find($productId);
        
        if (!$product) {
            $this->error("Товар с ID {$productId} не найден!");
            return 1;
        }
        
        $this->info("Товар: {$product->name} (SKU: {$product->sku})");
        $this->info("Текущий статус: {$product->status->name}");
        
        // Находим статус
        $targetStatus = ProductStatus::where('name', $status)->first();
        
        if (!$targetStatus) {
            $this->error("Статус '{$status}' не найден в базе данных!");
            return 1;
        }
        
        $this->info("Найден статус {$status}: {$targetStatus->name} (ID: {$targetStatus->id})");
        
        // Показываем связанные объявления
        $advertisements = $product->advertisements;
        $this->info("Связанные объявления: {$advertisements->count()}");
        
        foreach ($advertisements as $advertisement) {
            $this->info("  - Объявление ID {$advertisement->id}: {$advertisement->title} (Статус: {$advertisement->status->name})");
        }
        
        // Показываем текущие задачи товара
        $actions = $product->actions;
        $this->info("Текущие задачи товара: {$actions->count()}");
        
        foreach ($actions as $action) {
            $this->info("  - Задача ID {$action->id}: {$action->action} (Срок: {$action->expired_at})");
        }
        
        // Спрашиваем подтверждение
        if (!$this->confirm("Продолжить с переводом товара в статус {$status}?")) {
            $this->info('Операция отменена.');
            return 0;
        }
        
        // Выполняем перевод в статус
        $this->info("Переводим товар в статус {$status}...");
        
        try {
            // Обновляем статус товара
            $oldStatus = $product->status;
            $product->update(['status_id' => $targetStatus->id]);
            $product->load('status');
            
            $this->info("Статус товара изменен с '{$oldStatus->name}' на '{$product->status->name}'");
            
            // Определяем срок выполнения в зависимости от статуса
            $expiredAt = $status === 'Холд' ? now()->addMonths(3) : now()->addMonths(6);
            
            // Создаем задачу для товара
            $productAction = ProductAction::create([
                'product_id' => $product->id,
                'user_id' => $product->owner_id,
                'action' => 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.',
                'expired_at' => $expiredAt,
                'status' => false
            ]);
            
            $this->info("Создана задача для товара ID {$productAction->id} со сроком {$expiredAt}");
            
            // Определяем исключаемые статусы объявлений в зависимости от статуса товара
            if ($status === 'Холд') {
                $excludedAdvertisementStatuses = AdvertisementStatus::whereIn('name', ['Продано', 'Архив', 'Холд'])->pluck('id');
                $targetAdvertisementStatus = AdvertisementStatus::where('name', 'Холд')->first();
                $logMessage = "В связи с переводом товара в статус Холд, объявление переводится в статус Холд.";
            } else {
                $excludedAdvertisementStatuses = AdvertisementStatus::whereIn('name', ['Продано', 'Архив'])->pluck('id');
                $targetAdvertisementStatus = AdvertisementStatus::where('name', 'Архив')->first();
                $logMessage = "В связи с переводом товара в статус Отказ, объявление переводится в статус Архив.";
            }
            
            $advertisementsToUpdate = $product->advertisements()
                ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                ->get();
            
            $this->info("Найдено объявлений для обновления: {$advertisementsToUpdate->count()}");
            
            if ($targetAdvertisementStatus && $advertisementsToUpdate->count() > 0) {
                // Обновляем статусы объявлений
                $product->advertisements()
                    ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                    ->update(['status_id' => $targetAdvertisementStatus->id]);
                
                $this->info("Статусы объявлений обновлены на '{$targetAdvertisementStatus->name}'");
                
                // Создаем логи и задачи для объявлений
                $systemLogType = \App\Models\LogType::where('name', 'Системный')->first();
                
                foreach ($advertisementsToUpdate as $advertisement) {
                    // Создаем лог
                    $advLog = AdvLog::create([
                        'advertisement_id' => $advertisement->id,
                        'type_id' => $systemLogType ? $systemLogType->id : null,
                        'log' => $logMessage,
                        'user_id' => null
                    ]);
                    
                    // Создаем задачу
                    $advAction = AdvAction::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $advertisement->created_by,
                        'action' => 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.',
                        'expired_at' => $expiredAt,
                        'status' => false
                    ]);
                    
                    $this->info("  - Объявление ID {$advertisement->id}: создан лог ID {$advLog->id}, задача ID {$advAction->id}");
                }
            }
            
            $this->info("Операция завершена успешно!");
            
            // Показываем результаты
            $this->info("\nРезультаты операции:");
            $this->info("- Новый статус товара: {$product->status->name}");
            
            $newActions = $product->actions()->where('status', false)->get();
            $this->info("- Активных задач товара: {$newActions->count()}");
            
            $updatedAdvertisements = $product->advertisements()->where('status_id', $targetAdvertisementStatus->id)->get();
            $this->info("- Объявлений в статусе {$targetAdvertisementStatus->name}: {$updatedAdvertisements->count()}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Ошибка при выполнении операции: " . $e->getMessage());
            return 1;
        }
    }
} 
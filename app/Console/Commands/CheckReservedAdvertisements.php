<?php

namespace App\Console\Commands;

use App\Models\Advertisement;
use App\Models\AdvertisementStatus;
use App\Models\ProductStatus;
use App\Models\AdvLog;
use App\Models\AdvAction;
use App\Models\ProductLog;
use App\Models\ProductAction;
use App\Models\LogType;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckReservedAdvertisements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:check-reserved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет объявления в статусе "Резерв" и переводит их в "Ревизия" если прошло 7 дней';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начало проверки объявлений в статусе "Резерв"...');
        $this->newLine();

        // Получаем статусы
        $reserveStatus = AdvertisementStatus::where('name', 'Резерв')->first();
        $revisionStatus = AdvertisementStatus::where('name', 'Ревизия')->first();
        $productRevisionStatus = ProductStatus::where('name', 'Ревизия')->first();
        $systemLogType = LogType::where('name', 'Системный')->first();

        if (!$reserveStatus) {
            $this->error('Статус "Резерв" не найден для объявлений!');
            return 1;
        }

        if (!$revisionStatus) {
            $this->error('Статус "Ревизия" не найден для объявлений!');
            return 1;
        }

        if (!$productRevisionStatus) {
            $this->error('Статус "Ревизия" не найден для товаров!');
            return 1;
        }

        // Находим все объявления в статусе "Резерв", которые не обновлялись 7 дней
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        $advertisements = Advertisement::with(['product', 'product.owner'])
            ->where('status_id', $reserveStatus->id)
            ->where('updated_at', '<=', $sevenDaysAgo)
            ->get();

        if ($advertisements->isEmpty()) {
            $this->info('Объявления для обновления не найдены.');
            return 0;
        }

        $this->info("Найдено объявлений для обработки: {$advertisements->count()}");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($advertisements as $advertisement) {
            try {
                $this->info("Обработка объявления ID: {$advertisement->id} - {$advertisement->title}");

                // Проверяем наличие связанного товара
                if (!$advertisement->product) {
                    $this->warn("  Пропущено: объявление не связано с товаром");
                    $errorCount++;
                    continue;
                }

                $product = $advertisement->product;
                $daysInReserve = Carbon::parse($advertisement->updated_at)->diffInDays(Carbon::now());

                // Обновляем статус объявления
                $advertisement->update([
                    'status_id' => $revisionStatus->id
                ]);

                // Создаем лог для объявления
                AdvLog::create([
                    'advertisement_id' => $advertisement->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "Прошло {$daysInReserve} дней с момента резервирования данного объявления. Статус автоматически изменен на 'Ревизия'. Требуется актуализировать статус.",
                    'user_id' => null // От имени системы
                ]);

                // Обновляем статус товара
                $product->update([
                    'status_id' => $productRevisionStatus->id
                ]);

                // Создаем лог для товара
                ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "Прошло {$daysInReserve} дней с момента резервирования данного товара. Статус автоматически изменен на 'Ревизия'. Требуется актуализировать статус.",
                    'user_id' => null // От имени системы
                ]);

                // Создаем задачу для менеджера товара
                if ($product->owner_id) {
                    ProductAction::create([
                        'product_id' => $product->id,
                        'user_id' => $product->owner_id,
                        'action' => "Прошло {$daysInReserve} дней с момента постановки товара в резерв. Требуется актуализация статуса.",
                        'expired_at' => Carbon::now()->addDays(1),
                        'status' => false
                    ]);

                    $this->info("  ✓ Создана задача для менеджера ID: {$product->owner_id}");
                } else {
                    $this->warn("  ! У товара нет назначенного владельца, задача не создана");
                }

                // // Создаем задачу для объявления (для создателя объявления)
                // if ($advertisement->created_by) {
                //     AdvAction::create([
                //         'advertisement_id' => $advertisement->id,
                //         'user_id' => $advertisement->created_by,
                //         'action' => "Прошло {$daysInReserve} дней с момента постановки объявления в резерв. Требуется актуализация статуса.",
                //         'expired_at' => Carbon::now()->addDays(1),
                //         'status' => false
                //     ]);

                //     $this->info("  ✓ Создана задача для создателя объявления ID: {$advertisement->created_by}");
                // }

                $this->info("  ✓ Статусы успешно обновлены: Резерв → Ревизия");
                $this->newLine();

                $successCount++;

            } catch (\Exception $e) {
                $this->error("  ✗ Ошибка при обработке объявления ID {$advertisement->id}: {$e->getMessage()}");
                $this->newLine();
                $errorCount++;
            }
        }

        // Итоговая статистика
        $this->newLine();
        $this->info('=== Результаты обработки ===');
        $this->info("Всего найдено: {$advertisements->count()}");
        $this->info("Успешно обработано: {$successCount}");
        
        if ($errorCount > 0) {
            $this->warn("Ошибок: {$errorCount}");
        }

        $this->newLine();
        $this->info('Проверка завершена.');

        return 0;
    }
}


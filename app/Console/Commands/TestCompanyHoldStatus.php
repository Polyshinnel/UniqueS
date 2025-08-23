<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Product;
use App\Models\Advertisement;
use App\Models\CompanyStatuses;
use App\Models\ProductStatus;
use App\Models\AdvertisementStatus;
use App\Models\CompanyActions;
use App\Models\ProductAction;
use App\Models\AdvAction;
use App\Models\CompanyLog;
use App\Models\ProductLog;
use App\Models\AdvLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestCompanyHoldStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-hold-status {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует перевод компании в статус "Холд" и связанную логику';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');

        if (!$companyId) {
            $this->error('Необходимо указать ID компании');
            return 1;
        }

        $company = Company::with(['status', 'products', 'products.advertisements'])->find($companyId);

        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена");
            return 1;
        }

        $this->info("Тестирование перевода компании '{$company->name}' в статус 'Холд'");
        $this->info("Текущий статус: {$company->status->name}");

        // Получаем пользователя для тестирования
        $user = User::first();
        if (!$user) {
            $this->error('Не найден пользователь для тестирования');
            return 1;
        }

        // Получаем статус "Холд"
        $holdStatus = CompanyStatuses::where('name', 'Холд')->first();
        if (!$holdStatus) {
            $this->error('Статус "Холд" не найден в базе данных');
            return 1;
        }

        $this->info("Начинаем тестирование...");

        try {
            DB::beginTransaction();

            // Сохраняем старый статус
            $oldStatus = $company->status;

            // Обновляем статус компании
            $company->update(['company_status_id' => $holdStatus->id]);
            $company->load('status');

            $this->info("Статус компании изменен с '{$oldStatus->name}' на '{$company->status->name}'");

            // Вызываем метод обработки статуса "Холд"
            $this->handleCompanyHoldStatus($company, $user);

            DB::commit();

            $this->info("Тестирование завершено успешно!");
            $this->info("Проверьте созданные задачи и логи в базе данных");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ошибка при тестировании: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Обрабатывает перевод компании в статус "Холд" (копия метода из контроллера)
     */
    private function handleCompanyHoldStatus(Company $company, $user)
    {
        $this->info("1. Создаем задачу для компании...");
        
        // 1. Создаем задачу для компании со сроком сейчас + 3 месяца
        $expiredAt = now()->addMonths(3);
        
        $companyAction = CompanyActions::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и ценам',
            'expired_at' => $expiredAt,
            'status' => false
        ]);

        $this->info("   Создана задача для компании: {$companyAction->action}");
        $this->info("   Срок выполнения: {$expiredAt->format('d.m.Y')}");

        // 2. Получаем статусы товаров, которые НЕ нужно переводить в "Холд"
        $excludedProductStatuses = ProductStatus::whereIn('name', ['Продано', 'Холд', 'Отказ'])->pluck('id');

        // 3. Получаем товары компании, которые нужно перевести в "Холд"
        $productsToUpdate = $company->products()
            ->whereNotIn('status_id', $excludedProductStatuses)
            ->get();

        $this->info("2. Найдено товаров для обновления: {$productsToUpdate->count()}");

        // 4. Получаем статус "Холд" для товаров
        $holdProductStatus = ProductStatus::where('name', 'Холд')->first();

        if ($holdProductStatus && $productsToUpdate->count() > 0) {
            // Обновляем статусы товаров
            $updatedProductsCount = $company->products()
                ->whereNotIn('status_id', $excludedProductStatuses)
                ->update(['status_id' => $holdProductStatus->id]);

            $this->info("   Обновлен статус {$updatedProductsCount} товаров на 'Холд'");

            // 5. Создаем системные логи для каждого товара
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            foreach ($productsToUpdate as $product) {
                $this->info("   Обрабатываем товар: {$product->name}");

                // Создаем системный лог для товара
                $productLog = ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "В связи с переводом компании \"{$company->name}\" в статус Холд, товар переводится в статус холд.",
                    'user_id' => null // От имени системы
                ]);

                $this->info("     Создан системный лог для товара");

                // Создаем задачу для товара
                $productAction = ProductAction::create([
                    'product_id' => $product->id,
                    'user_id' => $product->owner_id,
                    'action' => 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.',
                    'expired_at' => $expiredAt,
                    'status' => false
                ]);

                $this->info("     Создана задача для товара: {$productAction->action}");

                // 6. Находим связанные объявления для товара
                $excludedAdvertisementStatuses = AdvertisementStatus::whereIn('name', ['Продано', 'Архив', 'Холд'])->pluck('id');
                
                $advertisementsToUpdate = $product->advertisements()
                    ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                    ->get();

                $this->info("     Найдено объявлений для обновления: {$advertisementsToUpdate->count()}");

                // 7. Получаем статус "Холд" для объявлений
                $holdAdvertisementStatus = AdvertisementStatus::where('name', 'Холд')->first();

                if ($holdAdvertisementStatus && $advertisementsToUpdate->count() > 0) {
                    // Обновляем статусы объявлений
                    $updatedAdvertisementsCount = $product->advertisements()
                        ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                        ->update(['status_id' => $holdAdvertisementStatus->id]);

                    $this->info("       Обновлен статус {$updatedAdvertisementsCount} объявлений на 'Холд'");

                    // 8. Создаем системные логи для каждого объявления
                    foreach ($advertisementsToUpdate as $advertisement) {
                        $this->info("       Обрабатываем объявление: {$advertisement->title}");

                        $advLog = AdvLog::create([
                            'advertisement_id' => $advertisement->id,
                            'type_id' => $systemLogType ? $systemLogType->id : null,
                            'log' => "В связи с переводом компании \"{$company->name}\" в статус Холд, объявление переводится в статус Холд.",
                            'user_id' => null // От имени системы
                        ]);

                        $this->info("         Создан системный лог для объявления");

                        // Создаем задачу для объявления
                        $advAction = AdvAction::create([
                            'advertisement_id' => $advertisement->id,
                            'user_id' => $advertisement->created_by,
                            'action' => 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.',
                            'expired_at' => $expiredAt,
                            'status' => false
                        ]);

                        $this->info("         Создана задача для объявления: {$advAction->action}");
                    }
                }
            }
        } else {
            $this->info("   Товары для обновления не найдены или статус 'Холд' не найден");
        }

        $this->info("Обработка завершена успешно!");
    }
} 
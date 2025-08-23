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

class TestCompanyRefuseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-refuse-status {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует перевод компании в статус "Отказ" и связанную логику';

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

        $this->info("Тестирование перевода компании '{$company->name}' в статус 'Отказ'");
        $this->info("Текущий статус: {$company->status->name}");

        // Получаем пользователя для тестирования
        $user = User::first();
        if (!$user) {
            $this->error('Не найден пользователь для тестирования');
            return 1;
        }

        // Получаем статус "Отказ"
        $refuseStatus = CompanyStatuses::where('name', 'Отказ')->first();
        if (!$refuseStatus) {
            $this->error('Статус "Отказ" не найден в базе данных');
            return 1;
        }

        $this->info("Начинаем тестирование...");

        try {
            DB::beginTransaction();

            // Сохраняем старый статус
            $oldStatus = $company->status;

            // Обновляем статус компании
            $company->update(['company_status_id' => $refuseStatus->id]);
            $company->load('status');

            $this->info("Статус компании изменен с '{$oldStatus->name}' на '{$company->status->name}'");

            // Вызываем метод обработки статуса "Отказ"
            $this->handleCompanyRefuseStatus($company, $user);

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
     * Обрабатывает перевод компании в статус "Отказ" (копия метода из контроллера)
     */
    private function handleCompanyRefuseStatus(Company $company, $user)
    {
        $this->info("1. Создаем задачу для компании...");
        
        // 1. Создаем задачу для компании со сроком сейчас + 6 месяцев
        $expiredAt = now()->addMonths(6);
        
        $companyAction = CompanyActions::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и ценам',
            'expired_at' => $expiredAt,
            'status' => false
        ]);

        $this->info("   Создана задача для компании: {$companyAction->action}");
        $this->info("   Срок выполнения: {$expiredAt->format('d.m.Y')}");

        // 2. Получаем статусы товаров, которые НЕ нужно переводить в "Отказ"
        $excludedProductStatuses = ProductStatus::whereIn('name', ['Продано', 'Отказ'])->pluck('id');

        // 3. Получаем товары компании, которые нужно перевести в "Отказ"
        $productsToUpdate = $company->products()
            ->whereNotIn('status_id', $excludedProductStatuses)
            ->get();

        $this->info("2. Найдено товаров для обновления: {$productsToUpdate->count()}");

        // 4. Получаем статус "Отказ" для товаров
        $refuseProductStatus = ProductStatus::where('name', 'Отказ')->first();

        if ($refuseProductStatus && $productsToUpdate->count() > 0) {
            // Обновляем статусы товаров
            $updatedProductsCount = $company->products()
                ->whereNotIn('status_id', $excludedProductStatuses)
                ->update(['status_id' => $refuseProductStatus->id]);

            $this->info("   Обновлен статус {$updatedProductsCount} товаров на 'Отказ'");

            // 5. Создаем системные логи для каждого товара
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            foreach ($productsToUpdate as $product) {
                $this->info("   Обрабатываем товар: {$product->name}");

                // Создаем системный лог для товара
                $productLog = ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "В связи с переводом компании \"{$company->name}\" в статус Отказ, товар переводится в статус Отказ.",
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
                $excludedAdvertisementStatuses = AdvertisementStatus::whereIn('name', ['Продано', 'Архив'])->pluck('id');
                
                $advertisementsToUpdate = $product->advertisements()
                    ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                    ->get();

                $this->info("     Найдено объявлений для обновления: {$advertisementsToUpdate->count()}");

                // 7. Получаем статус "Архив" для объявлений
                $archiveAdvertisementStatus = AdvertisementStatus::where('name', 'Архив')->first();

                if ($archiveAdvertisementStatus && $advertisementsToUpdate->count() > 0) {
                    // Обновляем статусы объявлений
                    $updatedAdvertisementsCount = $product->advertisements()
                        ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                        ->update(['status_id' => $archiveAdvertisementStatus->id]);

                    $this->info("       Обновлен статус {$updatedAdvertisementsCount} объявлений на 'Архив'");

                    // 8. Создаем системные логи для каждого объявления
                    foreach ($advertisementsToUpdate as $advertisement) {
                        $this->info("       Обрабатываем объявление: {$advertisement->title}");

                        $advLog = AdvLog::create([
                            'advertisement_id' => $advertisement->id,
                            'type_id' => $systemLogType ? $systemLogType->id : null,
                            'log' => "В связи с переводом компании \"{$company->name}\" в статус Отказ, объявление переводится в статус Архив.",
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
            $this->info("   Товары для обновления не найдены или статус 'Отказ' не найден");
        }

        $this->info("Обработка завершена успешно!");
    }
} 
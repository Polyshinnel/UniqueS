<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Product;
use App\Models\CompanyStatuses;
use App\Models\ProductStatus;
use App\Models\CompanyActions;
use App\Models\CompanyLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestCompanyStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-status-update {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует обновление статуса компании и связанную логику';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        if ($companyId) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("Компания с ID {$companyId} не найдена");
                return 1;
            }
            $companies = collect([$company]);
        } else {
            $companies = Company::all();
            if ($companies->isEmpty()) {
                $this->error('Нет компаний в базе данных');
                return 1;
            }
        }

        $this->info("Найдено компаний: {$companies->count()}");
        
        // Получаем статусы
        $holdStatus = CompanyStatuses::where('name', 'Холд')->first();
        $refuseStatus = CompanyStatuses::where('name', 'Отказ')->first();
        
        if (!$holdStatus || !$refuseStatus) {
            $this->error('Статусы "Холд" или "Отказ" не найдены в базе данных');
            return 1;
        }

        $user = User::first();
        if (!$user) {
            $this->error('Нет пользователей в базе данных');
            return 1;
        }

        foreach ($companies as $company) {
            $this->info("\nТестируем компанию: {$company->name} (ID: {$company->id})");
            
            // Проверяем текущий статус
            $currentStatus = $company->status;
            $this->info("Текущий статус: {$currentStatus->name}");
            
            // Подсчитываем товары компании
            $productsCount = $company->products()->count();
            $this->info("Количество товаров: {$productsCount}");
            
            // Подсчитываем текущие действия
            $actionsCount = $company->actions()->where('status', false)->count();
            $this->info("Активных действий: {$actionsCount}");
            
            // Тестируем изменение на "Холд"
            $this->info("\n--- Тест изменения статуса на 'Холд' ---");
            $this->testStatusChange($company, $holdStatus, $user, 'Тестовый комментарий для Холд');
            
            // Тестируем изменение на "Отказ"
            $this->info("\n--- Тест изменения статуса на 'Отказ' ---");
            $this->testStatusChange($company, $refuseStatus, $user, 'Тестовый комментарий для Отказ');
            
            // Возвращаем исходный статус
            $company->update(['company_status_id' => $currentStatus->id]);
            $this->info("Статус возвращен к исходному: {$currentStatus->name}");
        }

        $this->info("\nТестирование завершено!");
        return 0;
    }

    private function testStatusChange($company, $newStatus, $user, $comment)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $company->status;
            $this->info("Меняем статус с '{$oldStatus->name}' на '{$newStatus->name}'");

            // Обновляем статус компании
            $company->update(['company_status_id' => $newStatus->id]);
            $company->load('status');

            // Проверяем, нужно ли обновить статусы товаров
            if (in_array($newStatus->name, ['Холд', 'Отказ'])) {
                $productStatus = ProductStatus::where('name', $newStatus->name)->first();
                
                if ($productStatus) {
                    $updatedProductsCount = $company->products()->update(['status_id' => $productStatus->id]);
                    $this->info("Обновлен статус {$updatedProductsCount} товаров на '{$newStatus->name}'");
                } else {
                    $this->warn("Статус товара '{$newStatus->name}' не найден");
                }
            }

            // Если статус "Отказ", создаем действие
            if ($newStatus->name === 'Отказ') {
                $expiredAt = now()->addMonths(6);
                
                CompanyActions::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'action' => 'Уточнить наличие станков и дальнейший статус компании',
                    'expired_at' => $expiredAt,
                    'status' => false
                ]);

                $this->info("Создано действие 'Уточнить наличие станков и дальнейший статус компании' со сроком до " . $expiredAt->format('d.m.Y'));
            }

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Смена статуса с '{$oldStatus->name}' на '{$newStatus->name}'. Комментарий: {$comment}";
                
                // Добавляем дополнительную информацию в лог
                if (isset($updatedProductsCount)) {
                    $productsLogText = $updatedProductsCount > 0 
                        ? " Обновлен статус {$updatedProductsCount} товаров на '{$newStatus->name}'."
                        : " Товары для обновления не найдены.";
                    $logText .= $productsLogText;
                }
                
                if ($newStatus->name === 'Отказ') {
                    $actionLogText = " Создано действие 'Уточнить наличие станков и дальнейший статус компании' со сроком до " . $expiredAt->format('d.m.Y') . ".";
                    $logText .= $actionLogText;
                }

                CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                $this->info("Создана запись в логе");
            }

            DB::commit();
            $this->info("Статус успешно обновлен");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ошибка при обновлении статуса: " . $e->getMessage());
        }
    }
}

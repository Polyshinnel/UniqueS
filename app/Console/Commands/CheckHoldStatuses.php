<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompanyStatuses;
use App\Models\ProductStatus;
use App\Models\AdvertisementStatus;
use App\Models\LogType;

class CheckHoldStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:hold-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет существование необходимых статусов для функциональности "Холд"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Проверка статусов для функциональности "Холд"');
        $this->info('==========================================');

        // Проверяем статусы компаний
        $this->info('1. Статусы компаний:');
        $companyStatuses = CompanyStatuses::all();
        foreach ($companyStatuses as $status) {
            $this->line("   - {$status->id}: {$status->name}");
        }

        $holdCompanyStatus = CompanyStatuses::where('name', 'Холд')->first();
        if ($holdCompanyStatus) {
            $this->info("   ✓ Статус 'Холд' для компаний найден (ID: {$holdCompanyStatus->id})");
        } else {
            $this->error("   ✗ Статус 'Холд' для компаний НЕ найден!");
        }

        // Проверяем статусы товаров
        $this->info('2. Статусы товаров:');
        $productStatuses = ProductStatus::all();
        foreach ($productStatuses as $status) {
            $this->line("   - {$status->id}: {$status->name}");
        }

        $holdProductStatus = ProductStatus::where('name', 'Холд')->first();
        if ($holdProductStatus) {
            $this->info("   ✓ Статус 'Холд' для товаров найден (ID: {$holdProductStatus->id})");
        } else {
            $this->error("   ✗ Статус 'Холд' для товаров НЕ найден!");
        }

        // Проверяем статусы объявлений
        $this->info('3. Статусы объявлений:');
        $advertisementStatuses = AdvertisementStatus::all();
        foreach ($advertisementStatuses as $status) {
            $this->line("   - {$status->id}: {$status->name}");
        }

        $holdAdvertisementStatus = AdvertisementStatus::where('name', 'Холд')->first();
        if ($holdAdvertisementStatus) {
            $this->info("   ✓ Статус 'Холд' для объявлений найден (ID: {$holdAdvertisementStatus->id})");
        } else {
            $this->error("   ✗ Статус 'Холд' для объявлений НЕ найден!");
        }

        // Проверяем типы логов
        $this->info('4. Типы логов:');
        $logTypes = LogType::all();
        foreach ($logTypes as $type) {
            $this->line("   - {$type->id}: {$type->name}");
        }

        $systemLogType = LogType::where('name', 'Системный')->first();
        if ($systemLogType) {
            $this->info("   ✓ Тип лога 'Системный' найден (ID: {$systemLogType->id})");
        } else {
            $this->error("   ✗ Тип лога 'Системный' НЕ найден!");
        }

        // Итоговая проверка
        $this->info('5. Итоговая проверка:');
        if ($holdCompanyStatus && $holdProductStatus && $holdAdvertisementStatus && $systemLogType) {
            $this->info('   ✓ Все необходимые статусы и типы логов найдены!');
            $this->info('   ✓ Функциональность "Холд" готова к использованию.');
        } else {
            $this->error('   ✗ Некоторые необходимые статусы или типы логов отсутствуют!');
            $this->error('   ✗ Функциональность "Холд" НЕ может работать корректно.');
            
            if (!$holdCompanyStatus) {
                $this->error('   - Отсутствует статус "Холд" для компаний');
            }
            if (!$holdProductStatus) {
                $this->error('   - Отсутствует статус "Холд" для товаров');
            }
            if (!$holdAdvertisementStatus) {
                $this->error('   - Отсутствует статус "Холд" для объявлений');
            }
            if (!$systemLogType) {
                $this->error('   - Отсутствует тип лога "Системный"');
            }
        }

        return 0;
    }
} 
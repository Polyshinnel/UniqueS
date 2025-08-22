<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\CompanyStatus;

class TestCompanyFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует фильтрацию компаний для создания товаров';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Тестирование фильтрации компаний для создания товаров');
        $this->line('');

        // Получаем ID статусов "Холд" и "Отказ"
        $holdStatusId = CompanyStatus::where('name', 'Холд')->value('id');
        $refuseStatusId = CompanyStatus::where('name', 'Отказ')->value('id');

        $this->info("Статус 'Холд' ID: " . ($holdStatusId ?? 'не найден'));
        $this->info("Статус 'Отказ' ID: " . ($refuseStatusId ?? 'не найден'));
        $this->line('');

        // Получаем компании без статусов "Холд" и "Отказ"
        $companies = Company::with(['status'])
            ->whereNotIn('company_status_id', [$holdStatusId, $refuseStatusId])
            ->get();

        $this->info("Доступные компании для создания товаров ({$companies->count()}):");
        foreach ($companies as $company) {
            $this->line("- {$company->name} (статус: {$company->status->name})");
        }

        $this->line('');

        // Получаем все компании для сравнения
        $allCompanies = Company::with(['status'])->get();

        $this->info("Все компании ({$allCompanies->count()}):");
        foreach ($allCompanies as $company) {
            $this->line("- {$company->name} (статус: {$company->status->name})");
        }

        $this->line('');
        $this->info('Тестирование завершено!');

        return 0;
    }
} 
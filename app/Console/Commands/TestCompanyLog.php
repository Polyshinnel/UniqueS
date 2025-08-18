<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Console\Command;

class TestCompanyLog extends Command
{
    protected $signature = 'test:company-log {company_id}';
    protected $description = 'Тестирует функциональность логов компаний';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        // Проверяем существование компании
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена");
            return 1;
        }

        $this->info("Компания: {$company->name}");

        // Проверяем типы логов
        $logTypes = LogType::all();
        $this->info("Доступные типы логов:");
        foreach ($logTypes as $type) {
            $this->line("  - {$type->name} (цвет: {$type->color})");
        }

        // Получаем последний лог
        $lastLog = CompanyLog::where('company_id', $companyId)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        if ($lastLog) {
            $this->info("\nПоследний лог:");
            $this->line("  Тип: " . ($lastLog->type ? $lastLog->type->name : 'Неизвестный тип'));
            $this->line("  Цвет: " . ($lastLog->type ? $lastLog->type->color : '#133E71'));
            $this->line("  Дата: " . $lastLog->created_at->format('d.m.Y H:i:s'));
            $this->line("  Сообщение: " . $lastLog->log);
            $this->line("  Создал: " . ($lastLog->user_id ? ($lastLog->user ? $lastLog->user->name : 'Пользователь не найден') : 'Система'));
        } else {
            $this->warn("Логи для компании не найдены");
        }

        return 0;
    }
}

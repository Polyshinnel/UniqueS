<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestLog extends Command
{
    protected $signature = 'create:test-log {company_id} {--type=1} {--user=null} {--message="Тестовый лог"}';
    protected $description = 'Создает тестовый лог для компании';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $typeId = $this->option('type');
        $userId = $this->option('user');
        $message = $this->option('message');
        
        // Проверяем существование компании
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена");
            return 1;
        }

        // Проверяем существование типа лога
        $logType = LogType::find($typeId);
        if (!$logType) {
            $this->error("Тип лога с ID {$typeId} не найден");
            return 1;
        }

        // Проверяем существование пользователя (если указан)
        if ($userId && $userId !== 'null') {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }
        }

        // Создаем лог
        $log = CompanyLog::create([
            'company_id' => $companyId,
            'user_id' => ($userId && $userId !== 'null') ? $userId : null,
            'type_id' => $typeId,
            'log' => $message
        ]);

        $this->info("Лог успешно создан:");
        $this->line("  ID: {$log->id}");
        $this->line("  Компания: {$company->name}");
        $this->line("  Тип: {$logType->name}");
        $this->line("  Сообщение: {$message}");
        $this->line("  Создал: " . ($log->user_id ? ($user ? $user->name : 'Пользователь не найден') : 'Система'));
        $this->line("  Дата: " . $log->created_at->format('d.m.Y H:i:s'));

        return 0;
    }
}

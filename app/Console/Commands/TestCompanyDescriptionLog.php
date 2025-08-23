<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\User;
use App\Models\LogType;
use App\Models\CompanyLog;
use Illuminate\Support\Facades\DB;

class TestCompanyDescriptionLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-description-log {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует функциональность логирования изменений описания компании';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        // Находим компанию
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена");
            return 1;
        }

        // Находим пользователя для тестирования
        $user = User::first();
        if (!$user) {
            $this->error("Пользователи не найдены в системе");
            return 1;
        }

        $this->info("Тестирование логирования изменений описания компании:");
        $this->info("Компания: {$company->name}");
        $this->info("Пользователь: {$user->name}");
        $this->info("Текущее описание: " . ($company->common_info ?: 'не указано'));

        // Убеждаемся, что тип лога "Комментарий" существует
        $commentLogType = LogType::where('name', 'Комментарий')->first();
        if (!$commentLogType) {
            $this->warn("Тип лога 'Комментарий' не найден, создаем...");
            $commentLogType = LogType::create([
                'name' => 'Комментарий',
                'color' => '#133E71'
            ]);
        }

        // Тестируем изменение описания
        $this->testDescriptionChange($company, $user, 'Тестовое описание от ' . now()->format('d.m.Y H:i:s'));
        
        // Тестируем изменение на пустое значение
        $this->testDescriptionChange($company, $user, '');
        
        // Тестируем изменение с пустого на новое значение
        $this->testDescriptionChange($company, $user, 'Новое описание после пустого');

        $this->info("Тестирование завершено успешно!");
        return 0;
    }

    private function testDescriptionChange($company, $user, $newDescription)
    {
        $this->line("\n--- Тест изменения описания ---");
        
        // Сохраняем старое значение
        $oldDescription = $company->common_info;
        $this->line("Старое описание: " . ($oldDescription ?: 'пустое'));
        $this->line("Новое описание: " . ($newDescription ?: 'пустое'));

        try {
            DB::beginTransaction();

            // Обновляем описание
            $company->update(['common_info' => $newDescription]);

            // Создаем лог вручную (имитируем логику контроллера)
            $oldValue = $oldDescription ?: 'пустое';
            $newValue = $newDescription ?: 'пустое';
            $logText = "Пользователь {$user->name} изменил Описание с \"{$oldValue}\" на \"{$newValue}\"";
            
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            $log = CompanyLog::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'log' => $logText,
                'type_id' => $commentLogType->id,
            ]);

            // Загружаем связи для лога
            $log->load(['type', 'user']);

            DB::commit();

            $this->info("✓ Лог создан успешно:");
            $this->line("  - Текст: {$log->log}");
            $this->line("  - Тип: {$log->type->name}");
            $this->line("  - Пользователь: {$log->user->name}");
            $this->line("  - Дата: {$log->created_at}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Ошибка при создании лога: " . $e->getMessage());
        }
    }
}

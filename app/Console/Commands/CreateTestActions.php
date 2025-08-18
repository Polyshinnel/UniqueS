<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\CompanyActions;
use App\Models\User;

class CreateTestActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-actions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает тестовые действия для компаний';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companies = Company::all();
        $users = User::all();

        if ($companies->isEmpty()) {
            $this->error('Нет компаний в базе данных');
            return 1;
        }

        if ($users->isEmpty()) {
            $this->error('Нет пользователей в базе данных');
            return 1;
        }

        $actions = [
            'Позвонить клиенту, уточнить по наличию оборудования',
            'Отправить коммерческое предложение',
            'Согласовать техническое задание',
            'Провести демонстрацию оборудования',
            'Подготовить договор',
            'Уточнить сроки поставки',
            'Согласовать условия оплаты',
            'Провести монтаж оборудования',
            'Обучить персонал работе с оборудованием',
            'Провести техническое обслуживание'
        ];

        $createdCount = 0;

        foreach ($companies as $company) {
            // Создаем 1-3 действия для каждой компании
            $actionCount = rand(1, 3);
            
            for ($i = 0; $i < $actionCount; $i++) {
                $action = CompanyActions::create([
                    'company_id' => $company->id,
                    'user_id' => $users->random()->id,
                    'action' => $actions[array_rand($actions)],
                    'expired_at' => now()->addDays(rand(1, 30)),
                    'status' => rand(0, 1) == 0, // 50% вероятность что действие не выполнено
                ]);
                
                $createdCount++;
            }
        }

        $this->info("Создано {$createdCount} тестовых действий для компаний");
        
        return 0;
    }
}

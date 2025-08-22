<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdvLog;
use App\Models\Advertisement;
use App\Models\LogType;
use App\Models\User;

class CreateTestAdvLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-adv-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает тестовые логи для объявлений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Создание тестовых логов для объявлений...');

        // Получаем или создаем тип лога
        $logType = LogType::firstOrCreate(
            ['name' => 'Комментарий'],
            ['name' => 'Комментарий']
        );

        // Получаем пользователя
        $user = User::first();
        if (!$user) {
            $this->error('Пользователь не найден. Создайте пользователя сначала.');
            return;
        }

        // Получаем объявления
        $advertisements = Advertisement::all();
        if ($advertisements->isEmpty()) {
            $this->error('Объявления не найдены. Создайте объявления сначала.');
            return;
        }

        $createdCount = 0;

        foreach ($advertisements as $advertisement) {
            // Создаем несколько логов для каждого объявления
            $logs = [
                [
                    'log' => "Объявление создано пользователем {$user->name}",
                    'type_id' => $logType->id,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
                [
                    'log' => "Объявление отредактировано: обновлены характеристики",
                    'type_id' => $logType->id,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
                [
                    'log' => "Добавлены новые медиафайлы к объявлению",
                    'type_id' => $logType->id,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
            ];

            foreach ($logs as $logData) {
                AdvLog::create($logData);
                $createdCount++;
            }
        }

        $this->info("Создано {$createdCount} тестовых логов для объявлений.");
    }
}

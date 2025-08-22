<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdvAction;
use App\Models\Advertisement;
use App\Models\User;

class CreateTestAdvAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-adv-action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает тестовые действия для объявлений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Создание тестовых действий для объявлений...');

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
            // Создаем несколько действий для каждого объявления
            $actions = [
                [
                    'action' => "Проверить актуальность объявления '{$advertisement->title}'",
                    'expired_at' => now()->addDays(3),
                    'status' => false,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
                [
                    'action' => "Обновить фотографии для объявления '{$advertisement->title}'",
                    'expired_at' => now()->addDays(7),
                    'status' => false,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
                [
                    'action' => "Проверить цены конкурентов для объявления '{$advertisement->title}'",
                    'expired_at' => now()->addDays(5),
                    'status' => false,
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisement->id,
                ],
            ];

            foreach ($actions as $actionData) {
                AdvAction::create($actionData);
                $createdCount++;
            }
        }

        $this->info("Создано {$createdCount} тестовых действий для объявлений.");
    }
}

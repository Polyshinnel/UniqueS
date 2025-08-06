<?php

namespace App\Console\Commands;

use App\Models\RoleList;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать пользователя с правами администратора';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Создание пользователя с правами администратора');
        $this->newLine();

        // Получаем роль администратора
        $adminRole = RoleList::where('name', 'Администратор')->first();
        
        if (!$adminRole) {
            $this->error('Роль "Администратор" не найдена в базе данных!');
            return 1;
        }

        // Запрашиваем данные пользователя
        $name = $this->ask('Введите имя пользователя');
        
        $email = $this->ask('Введите email пользователя');
        
        $phone = $this->ask('Введите телефон пользователя (необязательно)');
        
        $password = $this->secret('Введите пароль пользователя');
        
        $passwordConfirmation = $this->secret('Подтвердите пароль');

        // Валидация данных
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Проверяем, не существует ли уже пользователь с таким email
        if (User::where('email', $email)->exists()) {
            $this->error('Пользователь с таким email уже существует!');
            return 1;
        }

        // Создаем пользователя
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
                'has_whatsapp' => false,
                'has_telegram' => false,
                'active' => true,
            ]);

            $this->info('Пользователь успешно создан!');
            $this->newLine();
            $this->info("ID: {$user->id}");
            $this->info("Имя: {$user->name}");
            $this->info("Email: {$user->email}");
            $this->info("Телефон: " . ($user->phone ?: 'не указан'));
            $this->info("Роль: {$adminRole->name}");
            $this->info("Статус: " . ($user->active ? 'Активен' : 'Неактивен'));

            return 0;
        } catch (\Exception $e) {
            $this->error('Ошибка при создании пользователя: ' . $e->getMessage());
            return 1;
        }
    }
} 
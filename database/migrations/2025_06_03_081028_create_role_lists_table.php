<?php

use App\Models\RoleList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            /**
             * Возможности просмотра данных
             * 0 - нельзя просматривать раздел
             * 1 - можно смотреть только свои
             * 2 - ограниченная видимость чужих
             * 3 - полный доступ
             */
            $table->integer('can_view_companies');
            $table->integer('can_view_products');
            $table->integer('can_view_advertise');
            $table->timestamps();
        });

        $roles = [
            [
                'name' => 'Администратор',
                'can_view_companies' => 3,
                'can_view_products' => 3,
                'can_view_advertise' => 3
            ],
            [
                'name' => 'Менеджер',
                'can_view_companies' => 1,
                'can_view_products' => 1,
                'can_view_advertise' => 2
            ],
            [
                'name' => 'Региональный представитель',
                'can_view_companies' => 0,
                'can_view_products' => 1,
                'can_view_advertise' => 0
            ]
        ];

        foreach ($roles as $role) {
            RoleList::create($role);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_lists');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Проверяем, есть ли уже данные в таблице
        if (DB::table('product_statuses')->count() == 0) {
            DB::table('product_statuses')->insert([
                [
                    'id' => 1,
                    'name' => 'С проверкой',
                    'color' => '#28a745',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'name' => 'Без проверки',
                    'color' => '#ffc107',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'name' => 'Возможно подключение',
                    'color' => '#17a2b8',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем только те записи, которые были добавлены в этой миграции
        DB::table('product_statuses')->whereIn('id', [1, 2, 3])->delete();
    }
};

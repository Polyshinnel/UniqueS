<?php

use App\Models\ProductCheckStatuses;
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
        Schema::create('product_check_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'С проверкой',
                'color' => '#28a745',
            ],
            [
                'name' => 'Без проверки',
                'color' => '#ffc107',
            ],
            [
                'name' => 'Возможно подключение',
                'color' => '#17a2b8',
            ],
        ];
        foreach ($dataItems as $item)
        {
            ProductCheckStatuses::create($item);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_check_statuses');
    }
};

<?php

use App\Models\ProductPriceType;
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
        Schema::create('product_price_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'Наличные'
            ],
            [
                'name' => 'без НДС'
            ],
            [
                'name' => 'с НДС'
            ],
            [
                'name' => 'Комбинированная'
            ],
            [
                'name' => 'Другое'
            ],
        ];

        foreach ($dataItems as $item) {
            ProductPriceType::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_types');
    }
};

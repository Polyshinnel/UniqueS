<?php

use App\Models\ProductAvailable;
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
        Schema::create('product_availables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'В наличии'
            ],
            [
                'name' => 'Под заказ'
            ],
        ];

        foreach ($dataItems as $item) {
            ProductAvailable::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_availables');
    }
};

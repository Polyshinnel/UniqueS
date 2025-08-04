<?php

use App\Models\ProductStatus;
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
        Schema::create('product_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->boolean('active')->default(true);
            $table->boolean('must_active_adv')->default(true);
            $table->timestamps();
        });

        $dataList = [
            [
                'name' => 'В работе',
                'color' => '#FFA500',
                'active' => false,
                'must_active_adv' => false
            ],
            [
                'name' => 'В продаже',
                'color' => '#28A745',
                'active' => true,
                'must_active_adv' => true
            ],
            [
                'name' => 'Резерв',
                'color' => '#17A2B8',
                'active' => false,
                'must_active_adv' => false
            ],
            [
                'name' => 'Холд',
                'color' => '#FFC107',
                'active' => false,
                'must_active_adv' => false
            ],
            [
                'name' => 'Продано',
                'color' => '#6C757D',
                'active' => false,
                'must_active_adv' => false
            ],
            [
                'name' => 'Вторая очередь',
                'color' => '#6F42C1',
                'active' => false,
                'must_active_adv' => false
            ],
            [
                'name' => 'Отказ',
                'color' => '#DC3545',
                'active' => false,
                'must_active_adv' => false
            ]
        ];

        foreach ($dataList as $data) {
            ProductStatus::create($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_statuses');
    }
};

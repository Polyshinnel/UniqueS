<?php

use App\Models\AdvertisementStatus;
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
        Schema::create('advertisement_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'Ревизия',
                'color' => '#FFA500',
                'is_published' => false,
            ],
            [
                'name' => 'Активное',
                'color' => '#28A745',
                'is_published' => true,
            ],
            [
                'name' => 'Резерв',
                'color' => '#17A2B8',
                'is_published' => true,
            ],
            [
                'name' => 'Холд',
                'color' => '#FFC107',
                'is_published' => false,
            ],
            [
                'name' => 'Продано',
                'color' => '#6F42C1',
                'is_published' => true,
            ],
            [
                'name' => 'Архив',
                'color' => '#6C757D',
                'is_published' => false,
            ]
        ];

        foreach ($dataItems as $dataItem) {
            AdvertisementStatus::create($dataItem);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_statuses');
    }
};

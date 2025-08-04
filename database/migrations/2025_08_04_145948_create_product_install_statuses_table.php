<?php

use App\Models\ProductInstallStatuses;
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
        Schema::create('product_install_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'Поставщиком'
            ],
            [
                'name' => 'Поставщиком (за доп.плату)'
            ],
            [
                'name' => 'Клиентом'
            ],
            [
                'name' => 'Другое'
            ],
        ];
        foreach ($dataItems as $item)
        {
            ProductInstallStatuses::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_install_statuses');
    }
};

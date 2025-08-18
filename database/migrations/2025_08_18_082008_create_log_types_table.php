<?php

use App\Models\LogType;
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
        Schema::create('log_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'Комментарий',
                'color' => '#133E71',
            ],
            [
                'name' => 'Системный',
                'color' => '#6c757d',
            ]
        ];

        foreach ($dataItems as $dataItem) {
            LogType::create($dataItem);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_types');
    }
};

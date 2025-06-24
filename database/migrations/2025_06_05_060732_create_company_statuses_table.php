<?php

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
        Schema::create('company_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $companyStatuses = [
            [
                'name' => 'В работе',
                'color' => '#133E71',
                'active' => true,
            ],
            [
                'name' => 'Вторая очередь',
                'color' => '#35A645',
                'active' => true,
            ],
            [
                'name' => 'Холд',
                'color' => '#DF7F2B',
                'active' => true,
            ],
            [
                'name' => 'Отказ',
                'color' => '#F60F0F',
                'active' => true,
            ],
        ];

        foreach ($companyStatuses as $companyStatus) {
            DB::table('company_statuses')->insert($companyStatus);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_statuses');
    }
};

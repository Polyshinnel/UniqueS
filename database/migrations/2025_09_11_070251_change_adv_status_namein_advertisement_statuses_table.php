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
        $updateArr = [
            'Активное' => 'В продаже',
        ];

        foreach ($updateArr as $old => $new) {
            \App\Models\AdvertisementStatus::where(['name' => $old])->update(['name' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $updateArr = [
            'В продаже' => 'Активное',
        ];

        foreach ($updateArr as $old => $new) {
            \App\Models\AdvertisementStatus::where(['name' => $old])->update(['name' => $new]);
        }
    }
};

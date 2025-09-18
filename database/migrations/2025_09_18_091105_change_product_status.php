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
        $mappings = [
            'В работе' => 'Ревизия'
        ];

        foreach ($mappings as $old => $new) {
            \App\Models\ProductStatus::where('name', $old)->update(['name' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $mappings = [
            'Ревизия' => 'В работе'
        ];

        foreach ($mappings as $old => $new) {
            \App\Models\ProductStatus::where('name', $old)->update(['name' => $new]);
        }
    }
};

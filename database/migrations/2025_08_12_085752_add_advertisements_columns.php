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
        Schema::table('advertisements', function (Blueprint $table) {
            $table->text('main_info')->after('technical_characteristics');
            $table->decimal('adv_price')->after('created_by');
            $table->string('adv_price_comment')->after('adv_price');
            $table->string('main_img')->after('adv_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn('main_info');
            $table->dropColumn('adv_price');
            $table->dropColumn('adv_price_comment');
            $table->dropColumn('main_img');
        });
    }
};

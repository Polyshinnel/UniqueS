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
            // Удаляем старые поля, если они существуют и больше не нужны
            // Эти поля заменены на JSON поля check_data, loading_data, removal_data
            if (Schema::hasColumn('advertisements', 'check_status_id')) {
                $table->dropColumn('check_status_id');
            }
            if (Schema::hasColumn('advertisements', 'check_comment')) {
                $table->dropColumn('check_comment');
            }
            if (Schema::hasColumn('advertisements', 'loading_status_id')) {
                $table->dropColumn('loading_status_id');
            }
            if (Schema::hasColumn('advertisements', 'loading_comment')) {
                $table->dropColumn('loading_comment');
            }
            if (Schema::hasColumn('advertisements', 'removal_status_id')) {
                $table->dropColumn('removal_status_id');
            }
            if (Schema::hasColumn('advertisements', 'removal_comment')) {
                $table->dropColumn('removal_comment');
            }
            if (Schema::hasColumn('advertisements', 'payment_types')) {
                $table->dropColumn('payment_types');
            }
            if (Schema::hasColumn('advertisements', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
            if (Schema::hasColumn('advertisements', 'payment_comment')) {
                $table->dropColumn('payment_comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            // Восстанавливаем старые поля при откате
            $table->unsignedBigInteger('check_status_id')->nullable();
            $table->text('check_comment')->nullable();
            $table->unsignedBigInteger('loading_status_id')->nullable();
            $table->text('loading_comment')->nullable();
            $table->unsignedBigInteger('removal_status_id')->nullable();
            $table->text('removal_comment')->nullable();
            $table->json('payment_types')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->text('payment_comment')->nullable();
        });
    }
};

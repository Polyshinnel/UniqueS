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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->text('status_comment')->nullable();
            $table->string('loading_type')->nullable();
            $table->text('loading_comment')->nullable();
            $table->string('removal_type')->nullable();
            $table->text('removal_comment')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->text('payment_comment')->nullable();
            
            // Добавляем foreign key для warehouse_id
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            
            // Делаем некоторые существующие поля nullable
            $table->text('main_chars')->nullable()->change();
            $table->text('mark')->nullable()->change();
            $table->text('complectation')->nullable()->change();
            $table->text('price_comment')->nullable()->change();
            $table->decimal('add_expenses')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn([
                'warehouse_id',
                'status_comment',
                'loading_type',
                'loading_comment',
                'removal_type',
                'removal_comment',
                'payment_method',
                'purchase_price',
                'payment_comment'
            ]);
        });
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('regional_id');
            $table->unsignedBigInteger('status_id');
            $table->text('main_chars');
            $table->text('mark');
            $table->text('complectation');
            $table->text('price_comment');
            $table->decimal('add_expenses');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('category_id')->references('id')->on('product_categories');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('regional_id')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('product_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

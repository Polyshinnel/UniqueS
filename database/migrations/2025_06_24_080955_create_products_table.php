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
            $table->string('product_address');

            $table->text('main_chars')->nullable();
            $table->text('mark')->nullable();
            $table->text('complectation')->nullable();
            $table->decimal('add_expenses', 12 ,2);

            $table->string('main_payment_method')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->text('payment_comment')->nullable();

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

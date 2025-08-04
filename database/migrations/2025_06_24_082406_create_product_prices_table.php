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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('price_type_id');
            $table->decimal('price', 8, 2)->default(0);
            $table->text('price_comment');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('price_type_id')->references('id')->on('product_price_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};

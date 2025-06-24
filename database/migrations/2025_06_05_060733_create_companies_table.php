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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('name');
            $table->string('inn')->nullable();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('regional_user_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->string('email');
            $table->string('site');
            $table->text('common_info');
            $table->unsignedBigInteger('company_status_id');
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('sources');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->foreign('regional_user_id')->references('id')->on('users');
            $table->foreign('owner_user_id')->references('id')->on('users');
            $table->foreign('company_status_id')->references('id')->on('company_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

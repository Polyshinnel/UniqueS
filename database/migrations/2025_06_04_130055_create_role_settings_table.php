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
        Schema::create('role_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->boolean('can_apply')->default(false);
            $table->boolean('view_foreign')->default(false);
            $table->boolean('is_tech')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_settings');
    }
};

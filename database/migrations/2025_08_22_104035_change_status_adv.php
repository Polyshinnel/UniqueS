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
            $table->dropColumn('status');
            $table->unsignedBigInteger('status_id')->after('removal_data')->default(1);

            $table->foreign('status_id')->references('id')->on('advertisement_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropForeign('advertisements_status_id_foreign');
            $table->dropColumn('status_id');
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft')->after('removal_data');
        });

    }
};

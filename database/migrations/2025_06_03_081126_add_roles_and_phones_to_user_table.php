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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->unsignedBigInteger('role_id')->nullable()->after('phone');
            $table->boolean('has_whatsapp')->default(false)->after('role_id');
            $table->boolean('has_telegram')->default(false)->after('has_whatsapp');

            $table->foreign('role_id')->references('id')->on('role_lists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('role_id');
            $table->dropColumn('has_whatsapp');
            $table->dropColumn('has_telegram');

            $table->dropForeign(['role_id']);
        });
    }
};

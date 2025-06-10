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
        Schema::create('company_contacts_phones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_contact_id');
            $table->string('phone');
            $table->timestamps();

            $table->foreign('company_contact_id')->references('id')->on('company_contacts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_contacts_phones');
    }
};

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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->text('manufacturer')->nullable();
            $table->string('name', 500);
            $table->string('reg_no', 50)->nullable();
            $table->string('generic_name', 500)->nullable();
            $table->string('strength', 500)->nullable();
            $table->string('dosage_form', 500)->nullable();
            $table->text('pack_size')->nullable();
            $table->string('licence_holder', 200)->nullable();
            $table->string('country_of_manufacture', 200)->nullable();
            $table->string('ltr', 200)->nullable();
            $table->integer('category')->nullable();
            $table->string('regn_date', 25)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
}; 
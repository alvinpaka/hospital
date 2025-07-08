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
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('drug_id')->nullable()->after('patient_id');
            $table->foreign('drug_id')->references('id')->on('drugs')->nullOnDelete();
            $table->dropColumn('medication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('medication');
            $table->dropForeign(['drug_id']);
            $table->dropColumn('drug_id');
        });
    }
}; 
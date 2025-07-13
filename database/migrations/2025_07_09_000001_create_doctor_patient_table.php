<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->nullable(); // e.g., doctor, nurse
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['doctor_id', 'patient_id']);
        });
        // Remove doctor_id from users table if exists
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'doctor_id')) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_patient');
        // Optionally, add doctor_id back to users table (not required for down)
    }
}; 
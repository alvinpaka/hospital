<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, paid, overdue
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
}; 
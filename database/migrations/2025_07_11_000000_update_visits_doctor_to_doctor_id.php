<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable doctor_id column if it doesn't exist
        if (!Schema::hasColumn('visits', 'doctor_id')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->foreignId('doctor_id')->nullable()->after('date')->constrained('users')->onDelete('cascade');
            });
        }

        // 2. Backfill doctor_id from doctor name
        $visits = DB::table('visits')->get();
        foreach ($visits as $visit) {
            if (isset($visit->doctor) && $visit->doctor) {
                $doctorUser = DB::table('users')->where('name', $visit->doctor)->first();
                if ($doctorUser) {
                    DB::table('visits')->where('id', $visit->id)->update(['doctor_id' => $doctorUser->id]);
                }
            }
        }

        // 3. Drop old doctor column if it exists
        if (Schema::hasColumn('visits', 'doctor')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->dropColumn('doctor');
            });
        }

        // 4. Make doctor_id non-nullable
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade')->change();
        });
    }

    public function down(): void
    {
        // Add doctor column back if it doesn't exist
        if (!Schema::hasColumn('visits', 'doctor')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->string('doctor')->nullable()->after('date');
            });
        }

        // Backfill doctor from doctor_id
        $visits = DB::table('visits')->get();
        foreach ($visits as $visit) {
            if (isset($visit->doctor_id) && $visit->doctor_id) {
                $doctorUser = DB::table('users')->where('id', $visit->doctor_id)->first();
                if ($doctorUser) {
                    DB::table('visits')->where('id', $visit->id)->update(['doctor' => $doctorUser->name]);
                }
            }
        }

        // Drop doctor_id column if it exists
        if (Schema::hasColumn('visits', 'doctor_id')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            });
        }
    }
}; 
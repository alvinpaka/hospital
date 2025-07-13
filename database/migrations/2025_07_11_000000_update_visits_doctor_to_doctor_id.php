<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The visits table already has doctor_id column, so we don't need to add it
        // Just mark this migration as completed since the structure is already correct
    }

    public function down(): void
    {
        // No down migration needed since we're not changing anything
    }
}; 
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vital;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables for a clean seed
        
        // User::factory(10)->create();

        // Users
        User::factory(10)->create();

        // Clinics
        \App\Models\Clinic::factory(10)->create();

        // Billings
        \App\Models\Billing::factory(20)->create();

        // Appointments
        \App\Models\Appointment::factory(20)->create();

        // Seeders
        $this->call([
            LabResultSeeder::class,
            PrescriptionSeeder::class,
            VisitSeeder::class,
            // ImmunizationSeeder::class, // Removed due to missing model
            StaffSeeder::class, // Register staff seeder
        ]);

        // Factories for other tables
        \App\Models\Vital::factory(20)->create();
        // Add more factories as needed for other tables
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run()
    {
        // Seed doctors
        User::factory()->count(5)->create([
            'role' => 'doctor',
            'password' => Hash::make('password'),
        ]);

        // Seed nurses
        User::factory()->count(5)->create([
            'role' => 'nurse',
            'password' => Hash::make('password'),
        ]);

        // Seed admin
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('adminpassword'),
        ]);
    }
} 
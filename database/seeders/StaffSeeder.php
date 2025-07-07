<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run()
    {
        // Seed doctors with custom names, emails, and phone numbers
        $doctors = [
            ['name' => 'Dr. Alice Smith', 'email' => 'alice.smith@hospital.com', 'phone' => '555-1001'],
            ['name' => 'Dr. Bob Johnson', 'email' => 'bob.johnson@hospital.com', 'phone' => '555-1002'],
            ['name' => 'Dr. Carol Lee', 'email' => 'carol.lee@hospital.com', 'phone' => '555-1003'],
            ['name' => 'Dr. David Kim', 'email' => 'david.kim@hospital.com', 'phone' => '555-1004'],
            ['name' => 'Dr. Eva Brown', 'email' => 'eva.brown@hospital.com', 'phone' => '555-1005'],
        ];
        foreach ($doctors as $doctor) {
            \App\Models\User::factory()->create(array_merge($doctor, [
                'role' => 'doctor',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]));
        }

        // Seed nurses with custom names, emails, and phone numbers
        $nurses = [
            ['name' => 'Nurse Frank Green', 'email' => 'frank.green@hospital.com', 'phone' => '555-2001'],
            ['name' => 'Nurse Grace Hall', 'email' => 'grace.hall@hospital.com', 'phone' => '555-2002'],
            ['name' => 'Nurse Henry Young', 'email' => 'henry.young@hospital.com', 'phone' => '555-2003'],
            ['name' => 'Nurse Irene King', 'email' => 'irene.king@hospital.com', 'phone' => '555-2004'],
            ['name' => 'Nurse Jack White', 'email' => 'jack.white@hospital.com', 'phone' => '555-2005'],
        ];
        foreach ($nurses as $nurse) {
            \App\Models\User::factory()->create(array_merge($nurse, [
                'role' => 'nurse',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]));
        }

        // Seed admin with custom name, email, and phone number
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@hospital.com',
            'phone' => '555-0000',
            'role' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('adminpassword'),
        ]);
    }
} 
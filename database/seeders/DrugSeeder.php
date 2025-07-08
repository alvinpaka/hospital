<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('drugs')->insert([
            [
                'manufacturer' => 'Pfizer',
                'name' => 'Panadol',
                'reg_no' => 'REG12345',
                'generic_name' => 'Paracetamol',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'pack_size' => '10 tablets',
                'licence_holder' => 'Pfizer Inc.',
                'country_of_manufacture' => 'USA',
                'ltr' => 'LTR001',
                'category' => 1,
                'regn_date' => '2020-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manufacturer' => 'GlaxoSmithKline',
                'name' => 'Augmentin',
                'reg_no' => 'REG67890',
                'generic_name' => 'Amoxicillin/Clavulanic acid',
                'strength' => '625mg',
                'dosage_form' => 'Tablet',
                'pack_size' => '14 tablets',
                'licence_holder' => 'GSK',
                'country_of_manufacture' => 'UK',
                'ltr' => 'LTR002',
                'category' => 2,
                'regn_date' => '2019-05-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manufacturer' => 'Cipla',
                'name' => 'Ciprofloxacin',
                'reg_no' => 'REG54321',
                'generic_name' => 'Ciprofloxacin',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'pack_size' => '10 tablets',
                'licence_holder' => 'Cipla Ltd.',
                'country_of_manufacture' => 'India',
                'ltr' => 'LTR003',
                'category' => 3,
                'regn_date' => '2021-03-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
} 
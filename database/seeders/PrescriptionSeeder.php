<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\User;

class PrescriptionSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        Prescription::factory()->count(10)->create(['patient_id' => $user->id]);
    }
}
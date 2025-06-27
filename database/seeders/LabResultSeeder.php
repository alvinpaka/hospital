<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabResult;
use App\Models\User;

class LabResultSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        LabResult::factory()->count(10)->create(['patient_id' => $user->id]);
    }
}

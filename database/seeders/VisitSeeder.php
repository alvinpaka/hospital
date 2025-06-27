<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visit;
use App\Models\User;

class VisitSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        Visit::factory()->count(10)->create(['patient_id' => $user->id]);
    }
}

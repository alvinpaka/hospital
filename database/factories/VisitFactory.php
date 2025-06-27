<?php
namespace Database\Factories;

use App\Models\Visit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'date' => $this->faker->date(),
            'doctor' => $this->faker->name,
            'department' => $this->faker->randomElement(['Cardiology', 'Neurology', 'Pediatrics']),
            'reason' => $this->faker->sentence(3),
            'summary' => $this->faker->paragraph,
        ];
    }
}
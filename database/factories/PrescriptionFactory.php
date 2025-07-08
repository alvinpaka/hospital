<?php
namespace Database\Factories;

use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'drug_id' => $this->faker->numberBetween(1, 3),
            'dosage' => $this->faker->randomElement(['5mg', '10mg', '20mg']),
            'instructions' => $this->faker->sentence,
            'doctor' => $this->faker->name,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
    }
}
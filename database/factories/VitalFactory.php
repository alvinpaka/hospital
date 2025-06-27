<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class VitalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => User::inRandomOrder()->first()?->id,
            'temperature' => $this->faker->randomFloat(1, 36, 39),
            'blood_pressure' => $this->faker->numberBetween(90, 140) . '/' . $this->faker->numberBetween(60, 90),
            'heart_rate' => $this->faker->numberBetween(60, 100),
            'respiratory_rate' => $this->faker->numberBetween(12, 20),
            'oxygen_saturation' => $this->faker->numberBetween(95, 100),
            'weight' => $this->faker->randomFloat(1, 40, 120),
            'height' => $this->faker->randomFloat(1, 140, 200),
            'fasting' => $this->faker->boolean(),
            'recorded_by' => $this->faker->name(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
} 
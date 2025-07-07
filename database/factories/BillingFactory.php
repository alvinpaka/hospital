<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Clinic;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'amount' => $this->faker->randomFloat(2, 20, 1000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
            'due_date' => $this->faker->optional()->date(),
            'paid_at' => $this->faker->optional()->dateTime(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}

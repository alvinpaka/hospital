<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'user_id' => \App\Models\User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'description' => $this->faker->randomElement([
                'Consultation Fee',
                'Lab Test',
                'Medication',
                'X-Ray',
                'Vaccination',
                'Room Charge',
                'Surgery',
            ]),
            'amount' => $this->faker->randomFloat(2, 20, 1000),
            'status' => $this->faker->randomElement(['Paid', 'Unpaid']),
            'invoice_url' => $this->faker->boolean(80) ? $this->faker->url() : null,
        ];
    }
}

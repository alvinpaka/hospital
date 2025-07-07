<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clinic;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Clinic',
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
} 
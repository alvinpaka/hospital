<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Appointment;
use App\Models\User;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'doctor_id' => User::factory(),
            'date' => $this->faker->date(),
            'time' => $this->faker->time('H:i'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'canceled']),
            'notes' => $this->faker->optional()->sentence(),
            'cancellation_reason' => $this->faker->optional()->sentence(),
        ];
    }
} 
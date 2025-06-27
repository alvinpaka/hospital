<?php
namespace Database\Factories;

use App\Models\Immunization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImmunizationFactory extends Factory
{
    protected $model = Immunization::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'vaccine' => $this->faker->word . ' Vaccine',
            'date' => $this->faker->date(),
            'provider' => $this->faker->company,
        ];
    }
}

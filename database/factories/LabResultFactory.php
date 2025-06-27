<?php
namespace Database\Factories;

use App\Models\LabResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabResultFactory extends Factory
{
    protected $model = LabResult::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'date' => $this->faker->date(),
            'type' => $this->faker->randomElement(['Blood Test', 'Urine Test', 'X-Ray', 'MRI']),
            'result' => $this->faker->randomElement(['Normal', 'Abnormal', 'Pending']),
            'doctor' => $this->faker->name,
            'notes' => $this->faker->sentence,
            'attachment_url' => null,
        ];
    }
}

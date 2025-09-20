<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'public_id' => Visit::generatePublicId(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'visit_type' => $this->faker->randomElement(['consultation', 'follow-up']),
            'consultation_fee' => $this->faker->randomFloat(2, 20, 200),
            'visit_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'diagnosis' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'visit_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }
}
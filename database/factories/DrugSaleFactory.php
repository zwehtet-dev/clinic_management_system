<?php

namespace Database\Factories;

use App\Models\DrugSale;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugSaleFactory extends Factory
{
    protected $model = DrugSale::class;

    public function definition(): array
    {
        return [
            'public_id' => DrugSale::generatePublicId(),
            'patient_id' => $this->faker->boolean(70) ? Patient::factory() : null, // 70% chance of having a patient
            'buyer_name' => function (array $attributes) {
                return $attributes['patient_id'] ? null : $this->faker->name();
            },
            'sale_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }

    public function withPatient(): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_id' => Patient::factory(),
            'buyer_name' => null,
        ]);
    }

    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_id' => null,
            'buyer_name' => $this->faker->name(),
        ]);
    }
}
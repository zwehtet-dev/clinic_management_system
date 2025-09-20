<?php

namespace Database\Factories;

use App\Models\DrugForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugFormFactory extends Factory
{
    protected $model = DrugForm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
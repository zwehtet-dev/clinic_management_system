<?php

namespace Database\Factories;

use App\Models\Drug;
use App\Models\DrugForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugFactory extends Factory
{
    protected $model = Drug::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['500mg', '250mg', '100mg']),
            'catelog' => $this->faker->optional()->word,
            'generic_name' => $this->faker->words(2, true),
            'drug_form_id' => DrugForm::factory(),
            'strength' => $this->faker->randomElement(['500mg', '250mg', '100mg', '50mg']),
            'unit' => $this->faker->randomElement(['tablet', 'capsule', 'ml', 'mg']),
            'min_stock' => $this->faker->numberBetween(10, 100),
            'expire_alert' => $this->faker->numberBetween(30, 90),
            'description' => $this->faker->optional()->sentence,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
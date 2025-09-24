<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        static $counter = 1;
        
        return [
            'public_id' => 'PAT-' . now()->year . '-' . str_pad($counter++, 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->name,
            'age' => $this->faker->numberBetween(1, 100),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'notes' => $this->faker->optional()->sentence,
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
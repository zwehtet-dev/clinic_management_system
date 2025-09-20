<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'public_id' => 'DOC-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => 'Dr. ' . $this->faker->name(),
            'specialization' => $this->faker->randomElement([
                'General Medicine',
                'Pediatrics',
                'Cardiology',
                'Dermatology',
                'Orthopedics',
                'Gynecology',
                'Neurology'
            ]),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'license_number' => 'LIC-' . $this->faker->unique()->numerify('######'),
            'notes' => $this->faker->optional()->sentence(),
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
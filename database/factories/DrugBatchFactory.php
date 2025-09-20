<?php

namespace Database\Factories;

use App\Models\Drug;
use App\Models\DrugBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugBatchFactory extends Factory
{
    protected $model = DrugBatch::class;

    public function definition(): array
    {
        $quantityReceived = $this->faker->numberBetween(50, 500);
        
        return [
            'drug_id' => Drug::factory(),
            'batch_number' => 'BATCH-' . $this->faker->unique()->numerify('######'),
            'purchase_price' => $this->faker->randomFloat(2, 10, 500),
            'sell_price' => $this->faker->randomFloat(2, 15, 600),
            'quantity_received' => $quantityReceived,
            'quantity_available' => $quantityReceived, // Initially all received quantity is available
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'received_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_available' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_available' => 0,
        ]);
    }
}
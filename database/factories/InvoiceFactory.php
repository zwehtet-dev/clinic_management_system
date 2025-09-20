<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\DrugSale;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoiceable_id' => DrugSale::factory(),
            'invoiceable_type' => DrugSale::class,
            'invoice_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['paid', 'cancelled']),
        ];
    }

    public function forVisit(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoiceable_id' => Visit::factory(),
            'invoiceable_type' => Visit::class,
        ]);
    }

    public function forDrugSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoiceable_id' => DrugSale::factory(),
            'invoiceable_type' => DrugSale::class,
        ]);
    }
}
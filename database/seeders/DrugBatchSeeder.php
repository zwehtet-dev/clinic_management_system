<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\DrugBatch;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DrugBatchSeeder extends Seeder
{
    public function run(): void
    {
        $drugs = Drug::all();

        foreach ($drugs as $drug) {
            // Create multiple batches for each drug with different scenarios
            
            // Batch 1: Good stock, not expiring soon
            DrugBatch::create([
                'drug_id' => $drug->id,
                'batch_number' => 'BATCH-' . $drug->id . '-001',
                'purchase_price' => rand(50, 500),
                'sell_price' => rand(100, 800),
                'quantity_received' => rand(100, 500),
                'quantity_available' => rand(80, 400),
                'expiry_date' => Carbon::now()->addMonths(rand(6, 24)),
                'received_date' => Carbon::now()->subDays(rand(1, 90)),
            ]);

            // Batch 2: Lower stock
            DrugBatch::create([
                'drug_id' => $drug->id,
                'batch_number' => 'BATCH-' . $drug->id . '-002',
                'purchase_price' => rand(50, 500),
                'sell_price' => rand(100, 800),
                'quantity_received' => rand(50, 200),
                'quantity_available' => rand(5, 50),
                'expiry_date' => Carbon::now()->addMonths(rand(3, 18)),
                'received_date' => Carbon::now()->subDays(rand(30, 120)),
            ]);

            // Batch 3: Expiring soon (for some drugs)
            if (rand(1, 3) === 1) {
                DrugBatch::create([
                    'drug_id' => $drug->id,
                    'batch_number' => 'BATCH-' . $drug->id . '-003',
                    'purchase_price' => rand(50, 500),
                    'sell_price' => rand(100, 800),
                    'quantity_received' => rand(20, 100),
                    'quantity_available' => rand(10, 80),
                    'expiry_date' => Carbon::now()->addDays(rand(1, 45)), // Expiring soon
                    'received_date' => Carbon::now()->subDays(rand(60, 180)),
                ]);
            }

            // Batch 4: Out of stock (for some drugs)
            if (rand(1, 4) === 1) {
                DrugBatch::create([
                    'drug_id' => $drug->id,
                    'batch_number' => 'BATCH-' . $drug->id . '-004',
                    'purchase_price' => rand(50, 500),
                    'sell_price' => rand(100, 800),
                    'quantity_received' => rand(50, 200),
                    'quantity_available' => 0, // Out of stock
                    'expiry_date' => Carbon::now()->addMonths(rand(6, 18)),
                    'received_date' => Carbon::now()->subDays(rand(90, 200)),
                ]);
            }

            // Batch 5: Expired (for some drugs)
            if (rand(1, 5) === 1) {
                DrugBatch::create([
                    'drug_id' => $drug->id,
                    'batch_number' => 'BATCH-' . $drug->id . '-005',
                    'purchase_price' => rand(50, 500),
                    'sell_price' => rand(100, 800),
                    'quantity_received' => rand(30, 150),
                    'quantity_available' => rand(10, 100),
                    'expiry_date' => Carbon::now()->subDays(rand(1, 90)), // Already expired
                    'received_date' => Carbon::now()->subDays(rand(200, 365)),
                ]);
            }
        }

        // Create some specific test scenarios
        $paracetamol = Drug::where('name', 'Paracetamol 500mg')->first();
        if ($paracetamol) {
            // Low stock scenario
            DrugBatch::create([
                'drug_id' => $paracetamol->id,
                'batch_number' => 'LOW-STOCK-001',
                'purchase_price' => 80,
                'sell_price' => 150,
                'quantity_received' => 500,
                'quantity_available' => 5, // Very low stock
                'expiry_date' => Carbon::now()->addMonths(8),
                'received_date' => Carbon::now()->subDays(30),
            ]);

            // Expiring soon scenario
            DrugBatch::create([
                'drug_id' => $paracetamol->id,
                'batch_number' => 'EXPIRING-001',
                'purchase_price' => 75,
                'sell_price' => 140,
                'quantity_received' => 200,
                'quantity_available' => 150,
                'expiry_date' => Carbon::now()->addDays(15), // Expiring in 15 days
                'received_date' => Carbon::now()->subDays(60),
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\DrugSale;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DrugSaleSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();

        // Create drug sales for the past 2 months
        for ($i = 0; $i < 80; $i++) {
            $saleDate = Carbon::now()->subDays(rand(1, 60));

            // 70% chance of having a patient, 30% walk-in
            $hasPatient = rand(1, 10) <= 7;

            DrugSale::create([
                'public_id' => DrugSale::generatePublicId(),
                'patient_id' => $hasPatient ? $patients->random()->id : null,
                'buyer_name' => $hasPatient ? null : $this->getRandomBuyerName(),
                'sale_date' => $saleDate
            ]);
        }

        // Create some specific test scenarios
        $testPatient = Patient::where('name', 'Ma Thida')->first();

        if ($testPatient) {
            // Recent sale with patient
            DrugSale::create([
                'public_id' => DrugSale::generatePublicId(),
                'patient_id' => $testPatient->id,
                'buyer_name' => null,
                'sale_date' => Carbon::now()->subDays(3),
            ]);
        }

        // Walk-in sales
        DrugSale::create([
            'public_id' => DrugSale::generatePublicId(),
            'patient_id' => null,
            'buyer_name' => 'Ko Thura',
            'sale_date' => Carbon::now()->subDays(1),
        ]);

        DrugSale::create([
            'public_id' => DrugSale::generatePublicId(),
            'patient_id' => null,
            'buyer_name' => 'Ma Sandar',
            'sale_date' => Carbon::today(),
        ]);

        // Today's sales for testing
        for ($i = 0; $i < 5; $i++) {
            DrugSale::create([
                'public_id' => DrugSale::generatePublicId(),
                'patient_id' => rand(1, 2) === 1 ? $patients->random()->id : null,
                'buyer_name' => rand(1, 2) === 1 ? null : $this->getRandomBuyerName(),
                'sale_date' => Carbon::today(),
            ]);
        }
    }

    private function getRandomBuyerName(): string
    {
        $names = [
            'Ko Aung',
            'Ma Mya',
            'U Thant',
            'Daw Khin',
            'Ko Zaw',
            'Ma Htwe',
            'U Maung',
            'Daw Aye',
            'Ko Min',
            'Ma Nwe',
            'Ko Thura',
            'Ma Sandar',
            'U Kyaw',
            'Daw Tin',
            'Ko Htet',
            'Ma Phyu',
            'U Win',
            'Daw Moe',
            'Ko Naing',
            'Ma Yee',
        ];

        return $names[array_rand($names)];
    }
}

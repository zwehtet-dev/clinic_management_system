<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific test patients with realistic data
        $patients = [
            [
                'public_id' => 'PAT-2025-000001',
                'name' => 'Aung Kyaw',
                'age' => 35,
                'gender' => 'male',
                'phone' => '+95-9-111111111',
                'address' => 'No. 123, Shwe Dagon Street, Yangon',
                'notes' => 'Diabetic patient, regular checkups required',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000002',
                'name' => 'Ma Thida',
                'age' => 28,
                'gender' => 'female',
                'phone' => '+95-9-222222222',
                'address' => 'No. 456, Golden Valley, Yangon',
                'notes' => 'Pregnant, expecting first child',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000003',
                'name' => 'Ko Zaw Min',
                'age' => 45,
                'gender' => 'male',
                'phone' => '+95-9-333333333',
                'address' => 'No. 789, University Avenue, Yangon',
                'notes' => 'Hypertension, on medication',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000004',
                'name' => 'Ma Khin Lay',
                'age' => 52,
                'gender' => 'female',
                'phone' => '+95-9-444444444',
                'address' => 'No. 321, Inya Lake Road, Yangon',
                'notes' => 'Arthritis, regular physiotherapy',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000005',
                'name' => 'Maung Htun',
                'age' => 67,
                'gender' => 'male',
                'phone' => '+95-9-555555555',
                'address' => 'No. 654, Kandawgyi Garden, Yangon',
                'notes' => 'Heart condition, regular monitoring',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000006',
                'name' => 'Ma Aye Aye',
                'age' => 23,
                'gender' => 'female',
                'phone' => '+95-9-666666666',
                'address' => 'No. 987, Botahtaung Township, Yangon',
                'notes' => 'Skin allergies, avoiding certain medications',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000007',
                'name' => 'Ko Thant Sin',
                'age' => 31,
                'gender' => 'male',
                'phone' => '+95-9-777777777',
                'address' => 'No. 147, Sanchaung Township, Yangon',
                'notes' => 'Sports injury recovery',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000008',
                'name' => 'Ma Nwe Nwe',
                'age' => 39,
                'gender' => 'female',
                'phone' => '+95-9-888888888',
                'address' => 'No. 258, Kamayut Township, Yangon',
                'notes' => 'Migraine sufferer, regular treatment',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000009',
                'name' => 'U Tin Maung',
                'age' => 72,
                'gender' => 'male',
                'phone' => '+95-9-999999999',
                'address' => 'No. 369, Bahan Township, Yangon',
                'notes' => 'Senior citizen, multiple conditions',
                'is_active' => true,
            ],
            [
                'public_id' => 'PAT-2025-000010',
                'name' => 'Ma Su Su',
                'age' => 26,
                'gender' => 'female',
                'phone' => '+95-9-101010101',
                'address' => 'No. 741, Mayangone Township, Yangon',
                'notes' => 'Young professional, stress-related issues',
                'is_active' => true,
            ],
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }

        // Create additional random patients for testing
        for ($i = 11; $i <= 50; $i++) {
            Patient::factory()->create([
                'public_id' => 'PAT-2025-' . str_pad($i, 6, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
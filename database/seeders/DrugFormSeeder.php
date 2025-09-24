<?php

namespace Database\Seeders;

use App\Models\DrugForm;
use Illuminate\Database\Seeder;

class DrugFormSeeder extends Seeder
{
    public function run(): void
    {
        $drugForms = [
            [
                'name' => 'Tablet',
                'description' => 'Solid dosage form containing medicinal substances',
                'is_active' => true,
            ],
            [
                'name' => 'Capsule',
                'description' => 'Encapsulated medicinal substances',
                'is_active' => true,
            ],
            [
                'name' => 'Syrup',
                'description' => 'Liquid medicinal preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Injection',
                'description' => 'Injectable medicinal solution',
                'is_active' => true,
            ],
            [
                'name' => 'Cream',
                'description' => 'Topical medicinal preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Drops',
                'description' => 'Liquid drops for eyes, ears, or nose',
                'is_active' => true,
            ],
            [
                'name' => 'Ointment',
                'description' => 'Semi-solid topical preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Powder',
                'description' => 'Dry medicinal powder',
                'is_active' => true,
            ],
        ];

        foreach ($drugForms as $form) {
            DrugForm::create($form);
        }
    }
}
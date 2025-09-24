<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\DrugForm;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $tabletForm = DrugForm::where('name', 'Tablet')->first();
        $capsuleForm = DrugForm::where('name', 'Capsule')->first();
        $syrupForm = DrugForm::where('name', 'Syrup')->first();
        $injectionForm = DrugForm::where('name', 'Injection')->first();
        $creamForm = DrugForm::where('name', 'Cream')->first();
        $dropsForm = DrugForm::where('name', 'Drops')->first();

        $drugs = [
            // Common medications
            [
                'name' => 'Paracetamol 500mg',
                'catelog' => 'PARA500',
                'generic_name' => 'Paracetamol',
                'drug_form_id' => $tabletForm->id,
                'strength' => '500mg',
                'unit' => 'tablet',
                'min_stock' => 100,
                'expire_alert' => 30,
                'description' => 'Pain reliever and fever reducer',
                'is_active' => true,
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'catelog' => 'AMOX250',
                'generic_name' => 'Amoxicillin',
                'drug_form_id' => $capsuleForm->id,
                'strength' => '250mg',
                'unit' => 'capsule',
                'min_stock' => 50,
                'expire_alert' => 60,
                'description' => 'Antibiotic for bacterial infections',
                'is_active' => true,
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'catelog' => 'IBU400',
                'generic_name' => 'Ibuprofen',
                'drug_form_id' => $tabletForm->id,
                'strength' => '400mg',
                'unit' => 'tablet',
                'min_stock' => 75,
                'expire_alert' => 30,
                'description' => 'Anti-inflammatory pain reliever',
                'is_active' => true,
            ],
            [
                'name' => 'Cough Syrup',
                'catelog' => 'COUGH100',
                'generic_name' => 'Dextromethorphan',
                'drug_form_id' => $syrupForm->id,
                'strength' => '100ml',
                'unit' => 'bottle',
                'min_stock' => 25,
                'expire_alert' => 90,
                'description' => 'Cough suppressant syrup',
                'is_active' => true,
            ],
            [
                'name' => 'Insulin Injection',
                'catelog' => 'INS10ML',
                'generic_name' => 'Human Insulin',
                'drug_form_id' => $injectionForm->id,
                'strength' => '10ml',
                'unit' => 'vial',
                'min_stock' => 20,
                'expire_alert' => 14,
                'description' => 'Diabetes medication',
                'is_active' => true,
            ],
            [
                'name' => 'Hydrocortisone Cream',
                'catelog' => 'HYDRO15G',
                'generic_name' => 'Hydrocortisone',
                'drug_form_id' => $creamForm->id,
                'strength' => '1%',
                'unit' => 'tube',
                'min_stock' => 30,
                'expire_alert' => 60,
                'description' => 'Topical anti-inflammatory cream',
                'is_active' => true,
            ],
            [
                'name' => 'Eye Drops',
                'catelog' => 'EYE10ML',
                'generic_name' => 'Chloramphenicol',
                'drug_form_id' => $dropsForm->id,
                'strength' => '0.5%',
                'unit' => 'bottle',
                'min_stock' => 15,
                'expire_alert' => 30,
                'description' => 'Antibiotic eye drops',
                'is_active' => true,
            ],
            [
                'name' => 'Aspirin 100mg',
                'catelog' => 'ASP100',
                'generic_name' => 'Acetylsalicylic Acid',
                'drug_form_id' => $tabletForm->id,
                'strength' => '100mg',
                'unit' => 'tablet',
                'min_stock' => 80,
                'expire_alert' => 30,
                'description' => 'Blood thinner and pain reliever',
                'is_active' => true,
            ],
            [
                'name' => 'Omeprazole 20mg',
                'catelog' => 'OME20',
                'generic_name' => 'Omeprazole',
                'drug_form_id' => $capsuleForm->id,
                'strength' => '20mg',
                'unit' => 'capsule',
                'min_stock' => 60,
                'expire_alert' => 45,
                'description' => 'Proton pump inhibitor for acid reflux',
                'is_active' => true,
            ],
            [
                'name' => 'Metformin 500mg',
                'catelog' => 'MET500',
                'generic_name' => 'Metformin HCl',
                'drug_form_id' => $tabletForm->id,
                'strength' => '500mg',
                'unit' => 'tablet',
                'min_stock' => 90,
                'expire_alert' => 60,
                'description' => 'Diabetes medication',
                'is_active' => true,
            ],
            [
                'name' => 'Cetirizine 10mg',
                'catelog' => 'CET10',
                'generic_name' => 'Cetirizine HCl',
                'drug_form_id' => $tabletForm->id,
                'strength' => '10mg',
                'unit' => 'tablet',
                'min_stock' => 50,
                'expire_alert' => 30,
                'description' => 'Antihistamine for allergies',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin C 500mg',
                'catelog' => 'VITC500',
                'generic_name' => 'Ascorbic Acid',
                'drug_form_id' => $tabletForm->id,
                'strength' => '500mg',
                'unit' => 'tablet',
                'min_stock' => 100,
                'expire_alert' => 90,
                'description' => 'Vitamin C supplement',
                'is_active' => true,
            ],
            [
                'name' => 'Multivitamin',
                'catelog' => 'MULTI',
                'generic_name' => 'Multivitamin Complex',
                'drug_form_id' => $tabletForm->id,
                'strength' => '1 tablet',
                'unit' => 'tablet',
                'min_stock' => 75,
                'expire_alert' => 120,
                'description' => 'Daily multivitamin supplement',
                'is_active' => true,
            ],
            [
                'name' => 'Antacid Syrup',
                'catelog' => 'ANT200ML',
                'generic_name' => 'Aluminum Hydroxide',
                'drug_form_id' => $syrupForm->id,
                'strength' => '200ml',
                'unit' => 'bottle',
                'min_stock' => 20,
                'expire_alert' => 60,
                'description' => 'Antacid for stomach acidity',
                'is_active' => true,
            ],
            [
                'name' => 'Diclofenac Gel',
                'catelog' => 'DICLO30G',
                'generic_name' => 'Diclofenac Sodium',
                'drug_form_id' => $creamForm->id,
                'strength' => '1%',
                'unit' => 'tube',
                'min_stock' => 25,
                'expire_alert' => 45,
                'description' => 'Topical anti-inflammatory gel',
                'is_active' => true,
            ],
        ];

        foreach ($drugs as $drug) {
            Drug::create($drug);
        }
    }
}
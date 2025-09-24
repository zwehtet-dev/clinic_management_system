<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'General Consultation',
                'price' => 15000.00,
                'description' => 'Basic medical consultation with general practitioner',
                'is_active' => true,
            ],
            [
                'name' => 'Specialist Consultation',
                'price' => 25000.00,
                'description' => 'Consultation with medical specialist',
                'is_active' => true,
            ],
            [
                'name' => 'Blood Pressure Check',
                'price' => 5000.00,
                'description' => 'Blood pressure measurement and monitoring',
                'is_active' => true,
            ],
            [
                'name' => 'Blood Sugar Test',
                'price' => 8000.00,
                'description' => 'Blood glucose level testing',
                'is_active' => true,
            ],
            [
                'name' => 'ECG Test',
                'price' => 20000.00,
                'description' => 'Electrocardiogram heart test',
                'is_active' => true,
            ],
            [
                'name' => 'X-Ray Chest',
                'price' => 30000.00,
                'description' => 'Chest X-ray examination',
                'is_active' => true,
            ],
            [
                'name' => 'Ultrasound Scan',
                'price' => 40000.00,
                'description' => 'Ultrasound imaging examination',
                'is_active' => true,
            ],
            [
                'name' => 'Complete Blood Count',
                'price' => 12000.00,
                'description' => 'Full blood count laboratory test',
                'is_active' => true,
            ],
            [
                'name' => 'Urine Analysis',
                'price' => 6000.00,
                'description' => 'Complete urine examination',
                'is_active' => true,
            ],
            [
                'name' => 'Vaccination',
                'price' => 18000.00,
                'description' => 'Immunization service',
                'is_active' => true,
            ],
            [
                'name' => 'Wound Dressing',
                'price' => 10000.00,
                'description' => 'Wound cleaning and dressing service',
                'is_active' => true,
            ],
            [
                'name' => 'Physiotherapy Session',
                'price' => 22000.00,
                'description' => 'Physical therapy treatment session',
                'is_active' => true,
            ],
            [
                'name' => 'Health Checkup Package',
                'price' => 80000.00,
                'description' => 'Comprehensive health screening package',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Consultation',
                'price' => 35000.00,
                'description' => 'Emergency medical consultation',
                'is_active' => true,
            ],
            [
                'name' => 'Follow-up Consultation',
                'price' => 10000.00,
                'description' => 'Follow-up visit after treatment',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
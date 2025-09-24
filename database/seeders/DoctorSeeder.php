<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            [
                'public_id' => 'DOC-2025-000001',
                'name' => 'Dr. Sarah Johnson',
                'specialization' => 'General Medicine',
                'license_number' => 'LIC-001234',
                'phone' => '+95-9-123456789',
                'address' => '123 Medical Street, Yangon',
                'notes' => 'Senior General Practitioner with 15 years experience',
                'is_active' => true,
            ],
            [
                'public_id' => 'DOC-2025-000002',
                'name' => 'Dr. Michael Chen',
                'specialization' => 'Pediatrics',
                'license_number' => 'LIC-001235',
                'phone' => '+95-9-123456790',
                'address' => '456 Children Ave, Yangon',
                'notes' => 'Specialist in child healthcare and development',
                'is_active' => true,
            ],
            [
                'public_id' => 'DOC-2025-000003',
                'name' => 'Dr. Emily Rodriguez',
                'specialization' => 'Cardiology',
                'license_number' => 'LIC-001236',
                'phone' => '+95-9-123456791',
                'address' => '789 Heart Center, Yangon',
                'notes' => 'Cardiologist with expertise in heart diseases',
                'is_active' => true,
            ],
            [
                'public_id' => 'DOC-2025-000004',
                'name' => 'Dr. James Wilson',
                'specialization' => 'Orthopedics',
                'license_number' => 'LIC-001237',
                'phone' => '+95-9-123456792',
                'address' => '321 Bone Clinic, Yangon',
                'notes' => 'Orthopedic surgeon specializing in joint replacement',
                'is_active' => true,
            ],
            [
                'public_id' => 'DOC-2025-000005',
                'name' => 'Dr. Lisa Thompson',
                'specialization' => 'Dermatology',
                'license_number' => 'LIC-001238',
                'phone' => '+95-9-123456793',
                'address' => '654 Skin Care Center, Yangon',
                'notes' => 'Dermatologist with focus on skin disorders',
                'is_active' => true,
            ],
            [
                'public_id' => 'DOC-2025-000006',
                'name' => 'Dr. Robert Kim',
                'specialization' => 'Neurology',
                'license_number' => 'LIC-001239',
                'phone' => '+95-9-123456794',
                'address' => '987 Brain Institute, Yangon',
                'notes' => 'Neurologist specializing in brain and nervous system',
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doctor) {
            Doctor::create($doctor);
        }
    }
}
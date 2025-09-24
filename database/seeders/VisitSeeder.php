<?php

namespace Database\Seeders;

use App\Models\Visit;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $doctors = Doctor::all();

        // Create visits for the past 3 months
        for ($i = 0; $i < 150; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            $visitDate = Carbon::now()->subDays(rand(1, 90));
            
            Visit::create([
                'public_id' => Visit::generatePublicId(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'visit_type' => rand(1, 4) === 1 ? 'follow-up' : 'consultation',
                'consultation_fee' => $this->getConsultationFee($doctor->specialization),
                'visit_date' => $visitDate,
                'diagnosis' => $this->getRandomDiagnosis(),
                'notes' => $this->getRandomNotes(),
                'status' => $this->getVisitStatus($visitDate),
            ]);
        }

        // Create some specific test scenarios
        $testPatient = Patient::where('name', 'Aung Kyaw')->first();
        $generalDoctor = Doctor::where('specialization', 'General Medicine')->first();
        
        if ($testPatient && $generalDoctor) {
            // Recent completed visit
            Visit::create([
                'public_id' => Visit::generatePublicId(),
                'patient_id' => $testPatient->id,
                'doctor_id' => $generalDoctor->id,
                'visit_type' => 'consultation',
                'consultation_fee' => 15000,
                'visit_date' => Carbon::now()->subDays(7),
                'diagnosis' => 'Type 2 Diabetes Mellitus - Follow up',
                'notes' => 'Blood sugar levels stable. Continue current medication. Next visit in 3 months.',
                'status' => 'completed',
            ]);

            // Upcoming follow-up visit
            Visit::create([
                'public_id' => Visit::generatePublicId(),
                'patient_id' => $testPatient->id,
                'doctor_id' => $generalDoctor->id,
                'visit_type' => 'follow-up',
                'consultation_fee' => 10000,
                'visit_date' => Carbon::now()->addDays(14),
                'diagnosis' => null,
                'notes' => 'Scheduled follow-up for diabetes monitoring',
                'status' => 'pending',
            ]);
        }

        // Create some pending visits for today and tomorrow
        for ($i = 0; $i < 10; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            Visit::create([
                'public_id' => Visit::generatePublicId(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'visit_type' => 'consultation',
                'consultation_fee' => $this->getConsultationFee($doctor->specialization),
                'visit_date' => rand(1, 2) === 1 ? Carbon::today() : Carbon::tomorrow(),
                'diagnosis' => null,
                'notes' => 'Scheduled appointment',
                'status' => 'pending',
            ]);
        }
    }

    private function getConsultationFee($specialization): float
    {
        return match ($specialization) {
            'General Medicine' => 15000,
            'Pediatrics' => 20000,
            'Cardiology' => 30000,
            'Orthopedics' => 25000,
            'Dermatology' => 22000,
            'Neurology' => 35000,
            default => 15000,
        };
    }

    private function getRandomDiagnosis(): string
    {
        $diagnoses = [
            'Upper Respiratory Tract Infection',
            'Hypertension',
            'Type 2 Diabetes Mellitus',
            'Gastroenteritis',
            'Migraine',
            'Lower Back Pain',
            'Allergic Rhinitis',
            'Urinary Tract Infection',
            'Anxiety Disorder',
            'Osteoarthritis',
            'Bronchitis',
            'Dermatitis',
            'Insomnia',
            'Acid Reflux',
            'Muscle Strain',
            'Viral Fever',
            'Conjunctivitis',
            'Sinusitis',
            'Tension Headache',
            'Skin Rash',
        ];

        return $diagnoses[array_rand($diagnoses)];
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Patient responded well to treatment. Continue current medication.',
            'Symptoms improving. Follow up in 2 weeks.',
            'Prescribed antibiotics. Complete full course.',
            'Lifestyle modifications recommended. Diet and exercise counseling provided.',
            'Blood pressure stable. Continue monitoring.',
            'Referred to specialist for further evaluation.',
            'Patient education provided. Return if symptoms worsen.',
            'Medication dosage adjusted. Monitor for side effects.',
            'Chronic condition management. Regular follow-ups needed.',
            'Acute symptoms resolved. Preventive measures discussed.',
            'Laboratory tests ordered. Results pending.',
            'Physical therapy recommended.',
            'Patient counseled on medication compliance.',
            'Symptoms under control. Continue current treatment plan.',
            'Emergency consultation. Immediate treatment provided.',
        ];

        return $notes[array_rand($notes)];
    }

    private function getVisitStatus($visitDate): string
    {
        if ($visitDate->isFuture()) {
            return 'pending';
        } elseif ($visitDate->isToday()) {
            return rand(1, 3) === 1 ? 'pending' : 'completed';
        } else {
            // Past visits
            $rand = rand(1, 10);
            if ($rand <= 8) return 'completed';
            if ($rand === 9) return 'cancelled';
            return 'pending'; // Some overdue visits
        }
    }
}
<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Visit Management', function () {
    it('can create a visit with valid data', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'visit_type' => 'consultation',
            'consultation_fee' => 50.00,
            'visit_date' => now()->toDateString(),
            'diagnosis' => 'Common cold',
            'notes' => 'Patient has mild symptoms',
            'status' => 'completed'
        ]);

        expect($visit)->toBeInstanceOf(Visit::class)
            ->and($visit->patient_id)->toBe($patient->id)
            ->and($visit->doctor_id)->toBe($doctor->id)
            ->and($visit->consultation_fee)->toBe('50.00')
            ->and($visit->public_id)->toMatch('/^VIS-\d{4}-\d{6}$/');
    });

    it('generates unique visit public IDs', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        
        $visit1 = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id
        ]);
        
        $visit2 = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id
        ]);

        expect($visit1->public_id)->not->toBe($visit2->public_id)
            ->and($visit1->public_id)->toMatch('/^VIS-\d{4}-\d{6}$/')
            ->and($visit2->public_id)->toMatch('/^VIS-\d{4}-\d{6}$/');
    });

    it('belongs to a patient and doctor', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id
        ]);

        expect($visit->patient)->toBeInstanceOf(Patient::class)
            ->and($visit->patient->id)->toBe($patient->id)
            ->and($visit->doctor)->toBeInstanceOf(Doctor::class)
            ->and($visit->doctor->id)->toBe($doctor->id);
    });

    it('can have an associated invoice', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id
        ]);

        expect($visit->invoice())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class);
    });

    it('can have doctor referral', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        
        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id
        ]);

        expect($visit->doctorReferral())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
    });

    it('validates consultation fee is numeric', function () {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $visit = Visit::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_fee' => 75.50
        ]);

        // This should be caught by validation in the actual form
        expect($visit->consultation_fee)->toBeNumeric();
    });
});
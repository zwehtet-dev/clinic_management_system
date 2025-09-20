<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Patient Registration', function () {
    it('can create a patient with valid data', function () {
        $patientData = [
            'public_id' => Patient::generatePublicId(),
            'name' => 'John Doe',
            'age' => 30,
            'gender' => 'male',
            'phone' => '09123456789',
            'address' => '123 Main St',
            'notes' => 'Test patient',
            'is_active' => true
        ];

        $patient = Patient::create($patientData);

        expect($patient)->toBeInstanceOf(Patient::class)
            ->and($patient->name)->toBe('John Doe')
            ->and($patient->age)->toBe(30)
            ->and($patient->gender)->toBe('male')
            ->and($patient->public_id)->toMatch('/^PAT-\d{4}-\d{6}$/');
    });

    it('generates unique public IDs for patients', function () {
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        expect($patient1->public_id)->not->toBe($patient2->public_id)
            ->and($patient1->public_id)->toMatch('/^PAT-\d{4}-\d{6}$/')
            ->and($patient2->public_id)->toMatch('/^PAT-\d{4}-\d{6}$/');
    });

    it('validates required fields', function () {
        expect(fn() => Patient::create([]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('can filter active patients', function () {
        Patient::factory()->create(['is_active' => true]);
        Patient::factory()->create(['is_active' => false]);

        $activePatients = Patient::active()->get();

        expect($activePatients)->toHaveCount(1)
            ->and($activePatients->first()->is_active)->toBeTrue();
    });
});

describe('Patient Relationships', function () {
    it('can have multiple visits', function () {
        $patient = Patient::factory()->create();
        
        expect($patient->visits())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});
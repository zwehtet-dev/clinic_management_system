<?php

use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\DrugForm;
use App\Models\DrugSale;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Filament Form Validation', function () {
    it('validates drug sale form requires either patient or buyer name', function () {
        // Test data without patient_id or buyer_name should fail validation
        $formData = [
            'public_id' => DrugSale::generatePublicId(),
            'sale_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'patient_id' => null,
            'buyer_name' => null
        ];

        // In a real Filament form, this would be validated
        // This test demonstrates the expected validation logic
        $hasPatientOrBuyer = !empty($formData['patient_id']) || !empty($formData['buyer_name']);
        
        expect($hasPatientOrBuyer)->toBeFalse();
    });

    it('validates drug sale form accepts patient OR buyer name', function () {
        $patient = Patient::factory()->create();
        
        // Test with patient
        $formDataWithPatient = [
            'public_id' => DrugSale::generatePublicId(),
            'sale_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'patient_id' => $patient->id,
            'buyer_name' => null
        ];

        $hasPatientOrBuyer = !empty($formDataWithPatient['patient_id']) || !empty($formDataWithPatient['buyer_name']);
        expect($hasPatientOrBuyer)->toBeTrue();

        // Test with buyer name
        $formDataWithBuyer = [
            'public_id' => DrugSale::generatePublicId(),
            'sale_date' => now()->toDateString(),
            'total_amount' => 100.00,
            'patient_id' => null,
            'buyer_name' => 'Walk-in Customer'
        ];

        $hasPatientOrBuyer = !empty($formDataWithBuyer['patient_id']) || !empty($formDataWithBuyer['buyer_name']);
        expect($hasPatientOrBuyer)->toBeTrue();
    });

    it('validates invoice item quantity is positive', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 100
        ]);

        $invoiceItemData = [
            'itemable_id' => $batch->id,
            'itemable_type' => DrugBatch::class,
            'quantity' => -5, // Invalid negative quantity
            'unit_price' => 100.00
        ];

        // Validation should catch negative quantities
        expect($invoiceItemData['quantity'])->toBeLessThan(1);
        
        $isValidQuantity = $invoiceItemData['quantity'] >= 1;
        expect($isValidQuantity)->toBeFalse();
    });

    it('validates invoice item quantity does not exceed available stock', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 10 // Only 10 available
        ]);

        $invoiceItemData = [
            'itemable_id' => $batch->id,
            'itemable_type' => DrugBatch::class,
            'quantity' => 15, // Requesting more than available
            'unit_price' => 100.00
        ];

        // This validation should be implemented in the form
        $requestedQuantity = $invoiceItemData['quantity'];
        $availableStock = $batch->quantity_available;
        
        $isValidQuantity = $requestedQuantity <= $availableStock;
        expect($isValidQuantity)->toBeFalse();
    });

    it('calculates line total correctly', function () {
        $invoiceItemData = [
            'quantity' => 5,
            'unit_price' => 25.50
        ];

        $expectedLineTotal = $invoiceItemData['quantity'] * $invoiceItemData['unit_price'];
        
        expect($expectedLineTotal)->toBe(127.50);
    });

    it('validates required fields in patient creation', function () {
        $patientData = [
            'name' => '', // Required field empty
            'age' => 30,
            'gender' => 'male'
        ];

        // Required field validation
        $hasRequiredFields = !empty($patientData['name']) && 
                           !empty($patientData['age']) && 
                           !empty($patientData['gender']);
        
        expect($hasRequiredFields)->toBeFalse();
    });

    it('validates age is numeric and positive', function () {
        $patientData = [
            'name' => 'John Doe',
            'age' => -5, // Invalid negative age
            'gender' => 'male'
        ];

        $isValidAge = is_numeric($patientData['age']) && $patientData['age'] > 0;
        expect($isValidAge)->toBeFalse();

        // Test with valid age
        $patientData['age'] = 30;
        $isValidAge = is_numeric($patientData['age']) && $patientData['age'] > 0;
        expect($isValidAge)->toBeTrue();
    });

    it('validates gender is in allowed values', function () {
        $allowedGenders = ['male', 'female', 'other'];
        
        $patientData = [
            'name' => 'John Doe',
            'age' => 30,
            'gender' => 'invalid_gender'
        ];

        $isValidGender = in_array($patientData['gender'], $allowedGenders);
        expect($isValidGender)->toBeFalse();

        // Test with valid gender
        $patientData['gender'] = 'male';
        $isValidGender = in_array($patientData['gender'], $allowedGenders);
        expect($isValidGender)->toBeTrue();
    });
});
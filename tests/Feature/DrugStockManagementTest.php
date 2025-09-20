<?php

use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\DrugForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Drug Stock Management', function () {
    it('calculates total stock correctly from available batches', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        
        // Create batches with different quantities
        DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 50,
            'expiry_date' => now()->addMonths(6)
        ]);
        
        DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 30,
            'expiry_date' => now()->addMonths(3)
        ]);
        
        // Expired batch should not be counted
        DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 20,
            'expiry_date' => now()->subDays(1)
        ]);

        expect($drug->total_stock)->toBe(80); // 50 + 30, expired batch excluded
    });

    it('identifies low stock drugs correctly', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create([
            'drug_form_id' => $drugForm->id,
            'min_stock' => 100
        ]);
        
        DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 50,
            'expiry_date' => now()->addMonths(6)
        ]);

        expect($drug->is_low_stock)->toBeTrue();
    });

    it('reduces stock correctly when selling drugs', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 100,
            'expiry_date' => now()->addMonths(6)
        ]);

        $result = $batch->reduceStock(30);

        expect($result)->toBeTrue()
            ->and($batch->fresh()->quantity_available)->toBe(70);
    });

    it('prevents overselling when stock is insufficient', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 10,
            'expiry_date' => now()->addMonths(6)
        ]);

        $result = $batch->reduceStock(20);

        expect($result)->toBeFalse()
            ->and($batch->fresh()->quantity_available)->toBe(10); // Stock unchanged
    });

    it('identifies expiring batches correctly', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create([
            'drug_form_id' => $drugForm->id,
            'expire_alert' => 30 // 30 days alert
        ]);
        
        $expiringBatch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'expiry_date' => now()->addDays(15) // Within alert period
        ]);
        
        $normalBatch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'expiry_date' => now()->addDays(60) // Outside alert period
        ]);

        expect($expiringBatch->is_expire_alert)->toBeTrue()
            ->and($normalBatch->is_expire_alert)->toBeFalse();
    });

    it('orders available batches by expiry date (FIFO)', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        
        $batch1 = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 50,
            'expiry_date' => now()->addMonths(6)
        ]);
        
        $batch2 = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 30,
            'expiry_date' => now()->addMonths(3)
        ]);

        $availableBatches = $drug->availableBatches()->get();

        expect($availableBatches->first()->id)->toBe($batch2->id) // Earlier expiry first
            ->and($availableBatches->last()->id)->toBe($batch1->id);
    });
});
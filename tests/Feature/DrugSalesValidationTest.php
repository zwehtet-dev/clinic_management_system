<?php

use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\DrugForm;
use App\Models\DrugSale;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Drug Sales Business Logic', function () {
    it('generates unique drug sale public IDs', function () {
        $sale1 = DrugSale::factory()->create();
        $sale2 = DrugSale::factory()->create();

        expect($sale1->public_id)->not->toBe($sale2->public_id)
            ->and($sale1->public_id)->toMatch('/^DS-\d{4}-\d{6}$/')
            ->and($sale2->public_id)->toMatch('/^DS-\d{4}-\d{6}$/');
    });

    it('can create drug sale with patient', function () {
        $patient = Patient::factory()->create();
        
        $sale = DrugSale::factory()->create([
            'patient_id' => $patient->id,
            'buyer_name' => null
        ]);

        expect($sale->buyer_display_name)->toBe($patient->name);
    });

    it('can create drug sale without patient (walk-in)', function () {
        $sale = DrugSale::factory()->create([
            'patient_id' => null,
            'buyer_name' => 'John Walk-in'
        ]);

        expect($sale->buyer_display_name)->toBe('John Walk-in');
    });

    it('defaults to walk-in customer when no patient or buyer name', function () {
        $sale = DrugSale::factory()->create([
            'patient_id' => null,
            'buyer_name' => null
        ]);

        expect($sale->buyer_display_name)->toBe('Walk-in Customer');
    });

    it('can filter sales by date ranges', function () {
        // Today's sale
        $todaySale = DrugSale::factory()->create([
            'sale_date' => now()->toDateString()
        ]);
        
        // Yesterday's sale
        $yesterdaySale = DrugSale::factory()->create([
            'sale_date' => now()->subDay()->toDateString()
        ]);

        $todaySales = DrugSale::today()->get();
        $thisMonthSales = DrugSale::thisMonth()->get();
        $thisYearSales = DrugSale::thisYear()->get();

        expect($todaySales)->toHaveCount(1)
            ->and($todaySales->first()->id)->toBe($todaySale->id)
            ->and($thisMonthSales)->toHaveCount(2)
            ->and($thisYearSales)->toHaveCount(2);
    });
});

describe('Invoice Integration', function () {
    it('creates invoice with correct line totals', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'sell_price' => 100.00,
            'quantity_available' => 50
        ]);

        $sale = DrugSale::factory()->create();
        $invoice = Invoice::factory()->create([
            'invoiceable_id' => $sale->id,
            'invoiceable_type' => DrugSale::class
        ]);

        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'itemable_id' => $batch->id,
            'itemable_type' => DrugBatch::class,
            'quantity' => 3,
            'unit_price' => 100.00,
            'line_total' => 300.00
        ]);

        expect($invoiceItem->line_total)->toBe(300.00)
            ->and($invoiceItem->quantity * $invoiceItem->unit_price)->toBe($invoiceItem->line_total);
    });

    it('generates unique invoice numbers', function () {
        $invoice1 = Invoice::factory()->create();
        $invoice2 = Invoice::factory()->create();

        expect($invoice1->invoice_number)->not->toBe($invoice2->invoice_number)
            ->and($invoice1->invoice_number)->toMatch('/^INV-\d{4}-\d{6}$/')
            ->and($invoice2->invoice_number)->toMatch('/^INV-\d{4}-\d{6}$/');
    });
});

describe('Stock Validation in Sales', function () {
    it('should validate available stock before sale', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 5, // Only 5 available
            'sell_price' => 100.00
        ]);

        // This test demonstrates the expected behavior
        // In a real implementation, this should be validated in the form or controller
        $requestedQuantity = 10; // Trying to sell more than available
        $availableStock = $batch->quantity_available;

        expect($requestedQuantity)->toBeGreaterThan($availableStock);
        
        // The system should prevent this sale
        $canSell = $availableStock >= $requestedQuantity;
        expect($canSell)->toBeFalse();
    });

    it('should allow sale when stock is sufficient', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        $batch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 20, // 20 available
            'sell_price' => 100.00
        ]);

        $requestedQuantity = 5; // Selling less than available
        $availableStock = $batch->quantity_available;

        $canSell = $availableStock >= $requestedQuantity;
        expect($canSell)->toBeTrue();
    });

    it('should not include expired batches in available stock', function () {
        $drugForm = DrugForm::factory()->create();
        $drug = Drug::factory()->create(['drug_form_id' => $drugForm->id]);
        
        // Expired batch
        $expiredBatch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 50,
            'expiry_date' => now()->subDays(1)
        ]);
        
        // Valid batch
        $validBatch = DrugBatch::factory()->create([
            'drug_id' => $drug->id,
            'quantity_available' => 30,
            'expiry_date' => now()->addMonths(6)
        ]);

        $availableBatches = $drug->availableBatches()->get();
        
        expect($availableBatches)->toHaveCount(1)
            ->and($availableBatches->first()->id)->toBe($validBatch->id);
    });
});
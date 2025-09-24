<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Visit;
use App\Models\DrugSale;
use App\Models\DrugBatch;
use App\Models\Service;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Create invoices for some visits
        $visits = Visit::where('status', 'completed')->take(50)->get();
        
        foreach ($visits as $visit) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoiceable_id' => $visit->id,
                'invoiceable_type' => Visit::class,
                'invoice_date' => $visit->visit_date,
                'total_amount' => 0, // Will be calculated
                'notes' => 'Invoice for medical consultation',
                'status' => 'paid',
            ]);

            $totalAmount = 0;

            // Add consultation fee as service
            $consultationService = Service::where('name', 'General Consultation')->first();
            if ($consultationService) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => Service::class,
                    'itemable_id' => $consultationService->id,
                    'quantity' => 1,
                    'unit_price' => $visit->consultation_fee,
                    'line_total' => $visit->consultation_fee,
                ]);
                $totalAmount += $visit->consultation_fee;
            }

            // Add some random services (30% chance)
            if (rand(1, 10) <= 3) {
                $randomService = Service::inRandomOrder()->first();
                if ($randomService) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_type' => Service::class,
                        'itemable_id' => $randomService->id,
                        'quantity' => 1,
                        'unit_price' => $randomService->price,
                        'line_total' => $randomService->price,
                    ]);
                    $totalAmount += $randomService->price;
                }
            }

            // Add some drugs (40% chance)
            if (rand(1, 10) <= 4) {
                $drugBatch = DrugBatch::where('quantity_available', '>', 5)
                    ->where('expiry_date', '>', now())
                    ->inRandomOrder()
                    ->first();
                
                if ($drugBatch) {
                    $quantity = rand(1, min(5, $drugBatch->quantity_available));
                    $lineTotal = $quantity * $drugBatch->sell_price;
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_type' => DrugBatch::class,
                        'itemable_id' => $drugBatch->id,
                        'quantity' => $quantity,
                        'unit_price' => $drugBatch->sell_price,
                        'line_total' => $lineTotal,
                    ]);
                    
                    $totalAmount += $lineTotal;
                    
                    // Reduce stock
                    $drugBatch->quantity_available -= $quantity;
                    $drugBatch->save();
                }
            }

            // Update invoice total
            $invoice->update(['total_amount' => $totalAmount]);
        }

        // Create invoices for drug sales
        $drugSales = DrugSale::take(30)->get();
        
        foreach ($drugSales as $drugSale) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoiceable_id' => $drugSale->id,
                'invoiceable_type' => DrugSale::class,
                'invoice_date' => $drugSale->sale_date,
                'total_amount' => 0, // Will be calculated
                'notes' => 'Invoice for drug purchase',
                'status' => 'paid',
            ]);

            $totalAmount = 0;
            $itemCount = rand(1, 4); // 1-4 different drugs per sale

            for ($i = 0; $i < $itemCount; $i++) {
                $drugBatch = DrugBatch::where('quantity_available', '>', 0)
                    ->where('expiry_date', '>', now())
                    ->inRandomOrder()
                    ->first();
                
                if ($drugBatch) {
                    $quantity = rand(1, min(10, $drugBatch->quantity_available));
                    $lineTotal = $quantity * $drugBatch->sell_price;
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_type' => DrugBatch::class,
                        'itemable_id' => $drugBatch->id,
                        'quantity' => $quantity,
                        'unit_price' => $drugBatch->sell_price,
                        'line_total' => $lineTotal,
                    ]);
                    
                    $totalAmount += $lineTotal;
                    
                    // Reduce stock
                    $drugBatch->quantity_available -= $quantity;
                    $drugBatch->save();
                }
            }

            // Update invoice and drug sale totals
            $invoice->update(['total_amount' => $totalAmount]);
            $drugSale->update(['total_amount' => $totalAmount]);
        }
    }
}
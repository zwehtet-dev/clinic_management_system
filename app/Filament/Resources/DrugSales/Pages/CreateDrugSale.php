<?php

namespace App\Filament\Resources\DrugSales\Pages;

use App\Filament\Resources\DrugSales\DrugSaleResource;
use App\Models\DrugBatch;
use App\Models\DrugSale;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDrugSale extends CreateRecord
{
    protected static string $resource = DrugSaleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Extract invoice data
        $createInvoice = $data['create_invoice'] ?? false;
        $invoiceData = $data['invoice'] ?? [];
        $invoiceItems = $invoiceData['invoice_items'] ?? [];

        // Remove invoice fields from drug sale data
        unset($data['create_invoice'], $data['invoice']);

        // Generate public ID for drug sale
        $data['public_id'] = DrugSale::generatePublicId();

        // Validate stock before creating sale
        if ($createInvoice && !empty($invoiceItems)) {
            $this->validateStockAvailability($invoiceItems);
        }

        // Use database transaction for data integrity
        return \DB::transaction(function () use ($data, $createInvoice, $invoiceData, $invoiceItems) {
            // Create the drug sale
            $drugSale = static::getModel()::create($data);

            // Create invoice if requested
            if ($createInvoice && !empty($invoiceItems)) {
                $this->createInvoiceForDrugSale($drugSale, $invoiceData);
            }

            return $drugSale;
        });
    }

    protected function validateStockAvailability(array $invoiceItems): void
    {
        foreach ($invoiceItems as $item) {
            if (isset($item['itemable_type']) && $item['itemable_type'] === DrugBatch::class) {
                $batch = DrugBatch::find($item['itemable_id']);
                if (!$batch) {
                    throw new \Exception("Drug batch not found.");
                }
                
                if ($batch->quantity_available < ($item['quantity'] ?? 1)) {
                    throw new \Exception("Insufficient stock for {$batch->drug->name}. Available: {$batch->quantity_available}, Requested: {$item['quantity']}");
                }
                
                if ($batch->expiry_date <= now()) {
                    throw new \Exception("Drug batch {$batch->batch_number} has expired.");
                }
            }
        }
    }

    protected function createInvoiceForDrugSale(DrugSale $drugSale, array $invoiceData): void
    {
        $invoiceItems = $invoiceData['invoice_items'] ?? [];
        $invoiceNotes = $invoiceData['notes'] ?? '';
        
        // Calculate total amount
        $totalAmount = collect($invoiceItems)->sum('line_total');

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceData['invoice_number'] ?? Invoice::generateInvoiceNumber(),
            'invoiceable_type' => DrugSale::class,
            'invoiceable_id' => $drugSale->id,
            'invoice_date' => $invoiceData['invoice_date'] ?? $drugSale->sale_date,
            'total_amount' => $totalAmount,
            'notes' => $invoiceNotes,
            'status' => 'paid',
        ]);

        // Create invoice items and reduce stock
        foreach ($invoiceItems as $item) {
            if (empty($item['itemable_type']) || empty($item['itemable_id'])) {
                continue;
            }

            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'itemable_type' => $item['itemable_type'],
                'itemable_id' => $item['itemable_id'],
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'line_total' => $item['line_total'] ?? 0,
            ]);

            // If it's a drug batch, reduce stock
            if ($item['itemable_type'] === DrugBatch::class) {
                $batch = DrugBatch::find($item['itemable_id']);
                if ($batch && !$batch->reduceStock($item['quantity'] ?? 1)) {
                    throw new \Exception("Failed to reduce stock for {$batch->drug->name}");
                }
            }
        }

        // Update drug sale total amount
        $drugSale->update(['total_amount' => $totalAmount]);

        // Send success notification
        Notification::make()
            ->title('Drug Sale and Invoice Created')
            ->success()
            ->body("Drug Sale {$drugSale->public_id} and Invoice {$invoice->invoice_number} created successfully.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

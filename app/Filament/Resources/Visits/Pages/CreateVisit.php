<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Visits\VisitResource;
use App\Models\DoctorReferral;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Extract invoice data
        $createInvoice = $data['create_invoice'] ?? false;
        $invoiceData = $data['invoice'] ?? [];
        $invoiceItems = $invoiceData['invoice_items'] ?? [];
        $invoiceNotes = $invoiceData['notes'] ?? '';

        // Remove invoice fields from visit data
        unset($data['create_invoice'], $data['invoice'], $data['selected_services']);

        // Generate public ID for visit
        $data['public_id'] = Visit::generatePublicId();

        // Validate stock before creating visit
        if ($createInvoice && !empty($invoiceItems)) {
            $this->validateStockAvailability($invoiceItems);
        }

        // Use database transaction for data integrity
        return \DB::transaction(function () use ($data, $createInvoice, $invoiceItems, $invoiceNotes) {
            // Create the visit
            $visit = static::getModel()::create($data);

            // Create doctor referral
            DoctorReferral::create([
                'doctor_id' => $visit->doctor->id,
                'visit_id' => $visit->id,
                'referral_fee' => $visit->consultation_fee,
                'status' => 'unpaid'
            ]);

            // Create invoice if requested
            if ($createInvoice && !empty($invoiceItems)) {
                $this->createInvoiceForVisit($visit, $invoiceItems, $invoiceNotes);
            }

            return $visit;
        });
    }

    protected function validateStockAvailability(array $invoiceItems): void
    {
        foreach ($invoiceItems as $item) {
            if (isset($item['itemable_type']) && $item['itemable_type'] === \App\Models\DrugBatch::class) {
                $batch = \App\Models\DrugBatch::find($item['itemable_id']);
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

    protected function createInvoiceForVisit(Visit $visit, array $invoiceItems, string $invoiceNotes = ''): void
    {
        $invoiceNumber = Invoice::generateInvoiceNumber();

        // Calculate total amount including consultation fee
        $itemsTotal = collect($invoiceItems)->sum('line_total');
        $totalAmount = $itemsTotal + $visit->consultation_fee;

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoiceable_type' => Visit::class,
            'invoiceable_id' => $visit->id,
            'invoice_date' => $visit->visit_date,
            'total_amount' => $totalAmount,
            'notes' => $invoiceNotes,
            'status' => 'paid',
        ]);

        // Create invoice items
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
            if ($item['itemable_type'] === \App\Models\DrugBatch::class) {
                $batch = \App\Models\DrugBatch::find($item['itemable_id']);
                if ($batch && !$batch->reduceStock($item['quantity'] ?? 1)) {
                    throw new \Exception("Failed to reduce stock for {$batch->drug->name}");
                }
            }
        }

        // Send success notification
        Notification::make()
            ->title('Visit and Invoice Created')
            ->success()
            ->body("Visit {$visit->public_id} and Invoice {$invoiceNumber} created successfully.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pre-fill patient_id if passed from URL (with validation)
        if (request()->has('patient_id')) {
            $patientId = request()->integer('patient_id');
            if ($patientId > 0 && \App\Models\Patient::where('id', $patientId)->exists()) {
                $data['patient_id'] = $patientId;
            }
        }

        // Pre-fill doctor_id if passed from URL (with validation)
        if (request()->has('doctor_id')) {
            $doctorId = request()->integer('doctor_id');
            if ($doctorId > 0 && \App\Models\Doctor::where('id', $doctorId)->exists()) {
                $data['doctor_id'] = $doctorId;
            }
        }

        return $data;
    }

}

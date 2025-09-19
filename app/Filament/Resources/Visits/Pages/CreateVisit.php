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
        $invoiceItems = $data['invoice_items'] ?? [];
        $invoiceNotes = $data['invoice_notes'] ?? '';

        // Remove invoice fields from visit data
        unset($data['create_invoice'], $data['invoice_items'], $data['invoice_notes']);

        // Generate public ID for visit
        $data['public_id'] = Visit::generatePublicId();

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
    }

     protected function createInvoiceForVisit(Visit $visit, array $invoiceItems, string $invoiceNotes = ''): void
    {

        $invoiceNumber = Invoice::generateInvoiceNumber();

        // Calculate total amount
        $totalAmount = collect($invoiceItems)->sum('line_total');

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
            if (empty($item['type']) || empty($item['item_id'])) {
                continue;
            }

            // Determine itemable type and id
            $itemableType = $item['type'] === 'service' ? Service::class : Drug::class;
            $itemableId = $item['item_id'];

            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'itemable_type' => $itemableType,
                'itemable_id' => $itemableId,
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'line_total' => $item['line_total'] ?? 0,
            ]);

            // If it's a drug, reduce stock
            if ($itemableType === Drug::class) {
                $drug = Drug::find($itemableId);
                if ($drug) {
                    $currentStock = (int) $drug->stock;
                    $newStock = max(0, $currentStock - ($item['quantity'] ?? 1));
                    $drug->update(['stock' => $newStock]);
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
        // Pre-fill patient_id if passed from URL
        if (request()->has('patient_id')) {
            $data['patient_id'] = request()->get('patient_id');
        }

        // Pre-fill doctor_id if passed from URL
        if (request()->has('doctor_id')) {
            $data['doctor_id'] = request()->get('doctor_id');
        }

        return $data;
    }

}

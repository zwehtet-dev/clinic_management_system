<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Models\Service;
use App\Models\DrugBatch;
use App\Models\InvoiceItem;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Symfony\Component\VarDumper\VarDumper;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $invoice = $this->record;

        // dd($data['drug_items']);

        // Create invoice items for drugs
        if (isset($data['drug_items']) && is_array($data['drug_items'])) {
            foreach ($data['drug_items'] as $drugItem) {

                // dd($data['drug_items']);

                if (isset($drugItem['batch_number']) &&
                    isset($drugItem['quantity']) &&
                    !empty($drugItem['batch_number']) &&
                    $drugItem['quantity'] > 0) {

                    $batch = DrugBatch::where('batch_number', $drugItem['batch_number'])->first();
                    // Create invoice item
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_id' => $batch->id,
                        'itemable_type' => $drugItem['itemable_type'] ?? DrugBatch::class,
                        'quantity' => $drugItem['quantity'],
                        'unit_price' => $drugItem['unit_price'] ?? 0,
                        'line_total' => $drugItem['line_total'] ?? 0,
                    ]);

                    // Reduce stock for drug batches
                    $itemableType = $drugItem['itemable_type'] ?? DrugBatch::class;
                    if ($itemableType === DrugBatch::class || $itemableType === 'App\\Models\\DrugBatch') {
                        if ($batch && $batch->quantity_available >= $drugItem['quantity']) {
                            $batch->decrement('quantity_available', $drugItem['quantity']);
                        }
                    }
                }
            }
        }

        // Create invoice items for services (only for visit invoices)
        if (isset($data['selected_services']) &&
            is_array($data['selected_services']) &&
            $data['invoiceable_type'] === \App\Models\Visit::class) {

            foreach ($data['selected_services'] as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'itemable_id' => $service->id,
                        'itemable_type' => Service::class,
                        'quantity' => 1,
                        'unit_price' => $service->price,
                        'line_total' => $service->price,
                    ]);
                }
            }
        }
    }
}

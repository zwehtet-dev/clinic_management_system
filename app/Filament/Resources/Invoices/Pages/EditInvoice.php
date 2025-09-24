<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Models\Service;
use App\Models\DrugBatch;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing invoice items into the form
        $invoice = $this->record;
        
        // Get drug items
        $drugItems = [];
        $selectedServices = [];
        
        foreach ($invoice->invoiceItems as $item) {
            if ($item->itemable_type === DrugBatch::class || $item->itemable_type === 'App\\Models\\DrugBatch') {
                $drugItems[] = [
                    'itemable_search' => $item->itemable_id,
                    'itemable_id' => $item->itemable_id,
                    'itemable_type' => $item->itemable_type,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ];
            } elseif ($item->itemable_type === Service::class || $item->itemable_type === 'App\\Models\\Service') {
                $selectedServices[] = $item->itemable_id;
            }
        }
        
        $data['drug_items'] = $drugItems;
        $data['selected_services'] = $selectedServices;
        
        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();
        $record = $this->record;

        // First, restore stock for existing drug items before deleting
        foreach ($record->invoiceItems()->whereHasMorph('itemable', [DrugBatch::class])->get() as $item) {
            $batch = DrugBatch::find($item->itemable_id);
            if ($batch) {
                $batch->increment('quantity_available', $item->quantity);
            }
        }

        // Delete existing invoice items
        $record->invoiceItems()->delete();

        // Create new invoice items for drugs
        if (isset($data['drug_items']) && is_array($data['drug_items'])) {
            foreach ($data['drug_items'] as $drugItem) {
                if (isset($drugItem['itemable_id']) && 
                    isset($drugItem['quantity']) && 
                    !empty($drugItem['itemable_id']) && 
                    $drugItem['quantity'] > 0) {
                    
                    // Create invoice item
                    InvoiceItem::create([
                        'invoice_id' => $record->id,
                        'itemable_id' => $drugItem['itemable_id'],
                        'itemable_type' => $drugItem['itemable_type'] ?? DrugBatch::class,
                        'quantity' => $drugItem['quantity'],
                        'unit_price' => $drugItem['unit_price'] ?? 0,
                        'line_total' => $drugItem['line_total'] ?? 0,
                    ]);

                    // Reduce stock for drug batches
                    $itemableType = $drugItem['itemable_type'] ?? DrugBatch::class;
                    if ($itemableType === DrugBatch::class || $itemableType === 'App\\Models\\DrugBatch') {
                        $batch = DrugBatch::find($drugItem['itemable_id']);
                        if ($batch && $batch->quantity_available >= $drugItem['quantity']) {
                            $batch->decrement('quantity_available', $drugItem['quantity']);
                        }
                    }
                }
            }
        }

        // Create new invoice items for services (only for visit invoices)
        if (isset($data['selected_services']) && 
            is_array($data['selected_services']) && 
            $data['invoiceable_type'] === \App\Models\Visit::class) {
            
            foreach ($data['selected_services'] as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    InvoiceItem::create([
                        'invoice_id' => $record->id,
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

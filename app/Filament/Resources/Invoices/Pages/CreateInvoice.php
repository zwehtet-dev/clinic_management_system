<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Models\Visit;
use App\Models\Service;
use App\Models\Setting;
use App\Models\DrugBatch;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Invoices\InvoiceResource;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    // protected function getCreateFormAction(): Action
    // {
    //     return Action::make('createInvoice')
    //         ->label('Create Invoice')
    //         ->requiresConfirmation()
    //         ->modalHeading('Receipt Preview')
    //         ->modalDescription('Review before creating')
    //         ->modalSubmitActionLabel('Create Invoice')
    //         ->modalCancelActionLabel('Go Back')
    //         ->modalWidth('lg')
    //         ->modalContent(function () {
    //             return $this->getThermalReceiptPreview();
    //         })
    //         ->action(fn () => $this->create());
    // }

    // protected function getThermalReceiptPreview(): HtmlString
    // {
    //     $data = $this->form->getState();

    //     // Get settings
    //     $clinicName = Setting::get('clinic_name', 'Medical Clinic');
    //     $clinicAddress = Setting::get('clinic_address', '');
    //     $clinicPhone = Setting::get('clinic_phone', '');
    //     $currencySymbol = Setting::get('currency_symbol', 'Ks');

    //     // Get reference data
    //     $type = $data['invoiceable_type'] ?? null;
    //     $invoiceableId = $data['invoiceable_id'] ?? null;

    //     $patientName = '';
    //     $patientPhone = '';
    //     $doctorName = '';

    //     if ($type === Visit::class && $invoiceableId) {
    //         $visit = Visit::with('patient', 'doctor')->find($invoiceableId);
    //         if ($visit) {
    //             $patientName = $visit->patient->name ?? '';
    //             $patientPhone = $visit->patient->phone ?? '';
    //             $doctorName = $visit->doctor ? 'Dr. ' . $visit->doctor->name : '';
    //         }
    //     } elseif ($type === \App\Models\DrugSale::class && $invoiceableId) {
    //         $drugSale = \App\Models\DrugSale::with('patient')->find($invoiceableId);
    //         if ($drugSale) {
    //             $patientName = $drugSale->patient ? $drugSale->patient->name : ($drugSale->buyer_name ?? '');
    //         }
    //     }

    //     $invoiceNumber = $data['invoice_number'] ?? '';
    //     $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'] ?? now())->format('d/m/Y H:i');
    //     $totalAmount = $data['total_amount'] ?? 0;
    //     $notes = $data['notes'] ?? '';

    //     // Process items
    //     $drugItems = $data['drug_items'] ?? [];
    //     $selectedServices = $data['selected_services'] ?? [];

    //     // Count valid items
    //     $validDrugs = 0;
    //     foreach ($drugItems as $item) {
    //         if (!empty($item['batch_number']) && !empty($item['quantity'])) {
    //             $validDrugs++;
    //         }
    //     }
    //     $totalItems = $validDrugs + count($selectedServices);

    //     // Build items list
    //     $itemsHtml = $this->buildItemsList($drugItems, $selectedServices);

    //     $html = <<<HTML
    //     <style>
    //         .receipt-wrapper {
    //             display: flex;
    //             justify-content: center;
    //             padding: 15px;
    //             background: #f9fafb;
    //         }
    //         .receipt {
    //             font-family: 'Courier New', monospace;
    //             font-size: 11px;
    //             line-height: 1.2;
    //             width: 80mm;
    //             background: white;
    //             padding: 8px;
    //             border: 1px solid #d1d5db;
    //             box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    //         }
    //         .r-header {
    //             text-align: center;
    //             border-bottom: 1px dashed #000;
    //             padding-bottom: 5px;
    //             margin-bottom: 8px;
    //         }
    //         .r-clinic {
    //             font-size: 14px;
    //             font-weight: bold;
    //             margin-bottom: 2px;
    //         }
    //         .r-info {
    //             font-size: 10px;
    //             margin-bottom: 1px;
    //         }
    //         .r-details {
    //             font-size: 10px;
    //             margin-bottom: 8px;
    //         }
    //         .r-details div {
    //             margin-bottom: 1px;
    //         }
    //         .r-sep {
    //             border-top: 1px dashed #000;
    //             margin: 5px 0;
    //         }
    //         .r-items {
    //             margin: 8px 0;
    //         }
    //         .r-item {
    //             display: flex;
    //             justify-content: space-between;
    //             font-size: 9px;
    //             margin-bottom: 2px;
    //             padding-bottom: 2px;
    //             border-bottom: 1px dotted #ddd;
    //         }
    //         .r-item-name {
    //             flex: 1;
    //             font-weight: bold;
    //             margin-right: 5px;
    //         }
    //         .r-item-qty {
    //             white-space: nowrap;
    //             font-size: 8px;
    //             color: #666;
    //         }
    //         .r-item-price {
    //             white-space: nowrap;
    //             font-weight: bold;
    //             min-width: 50px;
    //             text-align: right;
    //         }
    //         .r-section {
    //             font-weight: bold;
    //             font-size: 9px;
    //             margin: 5px 0 3px 0;
    //         }
    //         .r-total {
    //             border-top: 1px dashed #000;
    //             padding-top: 5px;
    //             margin-top: 8px;
    //             text-align: center;
    //             font-weight: bold;
    //             font-size: 12px;
    //         }
    //         .r-footer {
    //             text-align: center;
    //             font-size: 9px;
    //             border-top: 1px dashed #000;
    //             padding-top: 5px;
    //             margin-top: 8px;
    //         }
    //         .r-badge {
    //             text-align: center;
    //             margin-bottom: 8px;
    //         }
    //         .r-badge span {
    //             background: #fbbf24;
    //             color: #78350f;
    //             padding: 2px 8px;
    //             border-radius: 8px;
    //             font-size: 9px;
    //             font-weight: bold;
    //         }
    //     </style>

    //     <div class="receipt-wrapper">
    //         <div class="receipt">
    //             <div class="r-badge"><span>PREVIEW</span></div>

    //             <div class="r-header">
    //                 <div class="r-clinic">{$clinicName}</div>
    //     HTML;

    //     if ($clinicAddress) {
    //         $html .= "<div class=\"r-info\">{$clinicAddress}</div>";
    //     }
    //     if ($clinicPhone) {
    //         $html .= "<div class=\"r-info\">Tel: {$clinicPhone}</div>";
    //     }

    //     $html .= "</div><div class=\"r-details\">";
    //     $html .= "<div><strong>INVOICE: {$invoiceNumber}</strong></div>";
    //     $html .= "<div>Date: {$invoiceDate}</div>";

    //     if ($patientName) {
    //         $html .= "<div>Patient: {$patientName}</div>";
    //     }
    //     if ($patientPhone) {
    //         $html .= "<div>Phone: {$patientPhone}</div>";
    //     }
    //     if ($doctorName) {
    //         $html .= "<div>Doctor: {$doctorName}</div>";
    //     }

    //     $html .= "<div style=\"font-size: 8px; margin-top: 2px;\">Items: {$totalItems}</div>";
    //     $html .= "</div><div class=\"r-sep\"></div>";
    //     $html .= "<div class=\"r-items\">{$itemsHtml}</div>";
    //     $html .= "<div class=\"r-total\">TOTAL: {$currencySymbol} " . number_format($totalAmount, 0) . "</div>";

    //     if ($notes) {
    //         $html .= "<div class=\"r-footer\"><div>" . Str::limit($notes, 50) . "</div></div>";
    //     }

    //     $html .= "<div class=\"r-footer\"><div>Thank you for your visit!</div><div style=\"margin-top: 3px;\">{$invoiceDate}</div></div>";
    //     $html .= "</div></div>";

    //     return new HtmlString($html);
    // }

    // protected function buildItemsList(array $drugItems, array $selectedServices): string
    // {
    //     $html = '';

    //     // Medicines
    //     if (!empty($drugItems)) {
    //         // Count valid drug items
    //         $validCount = 0;
    //         foreach ($drugItems as $item) {
    //             if (!empty($item['batch_number']) && !empty($item['quantity'])) {
    //                 $validCount++;
    //             }
    //         }

    //         if ($validCount > 0) {
    //             $html .= '<div class="r-section">MEDICINES (' . $validCount . '):</div>';

    //             foreach ($drugItems as $item) {
    //                 // Skip if no itemable_id or no quantity
    //                 if (empty($item['batch_number']) || empty($item['quantity'])) {
    //                     continue;
    //                 }

    //                 $batch = DrugBatch::with('drug')->where('batch_number',$item['batch_number'])->first();
    //                 if (!$batch || !$batch->drug) {
    //                     continue;
    //                 }

    //                 $name = Str::limit($batch->drug->name, 20);
    //                 $qty = number_format($item['quantity']);
    //                 $price = number_format($item['unit_price'] ?? 0, 0);
    //                 $total = number_format($item['line_total'] ?? 0, 0);

    //                 $html .= <<<HTML
    //                 <div class="r-item">
    //                     <div class="r-item-name">{$name}</div>
    //                     <div class="r-item-qty">{$qty}x{$price}</div>
    //                     <div class="r-item-price">{$total}</div>
    //                 </div>
    //                 HTML;
    //             }
    //         }
    //     }

    //     // Services
    //     if (!empty($selectedServices)) {
    //         if (!empty($drugItems)) {
    //             $html .= '<div style="border-top: 1px dotted #999; margin: 3px 0;"></div>';
    //         }
    //         $html .= '<div class="r-section">SERVICES (' . count($selectedServices) . '):</div>';

    //         foreach ($selectedServices as $serviceId) {
    //             $service = Service::find($serviceId);
    //             if (!$service) continue;

    //             $name = Str::limit($service->name, 20);
    //             $price = number_format($service->price, 0);

    //             $html .= <<<HTML
    //             <div class="r-item">
    //                 <div class="r-item-name">{$name}</div>
    //                 <div class="r-item-qty">1x{$price}</div>
    //                 <div class="r-item-price">{$price}</div>
    //             </div>
    //             HTML;
    //         }
    //     }

    //     if (empty($html)) {
    //         $html = '<div style="text-align: center; color: #999; padding: 10px 0;">No items</div>';
    //     }

    //     return $html;
    // }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $invoice = $this->record;

        // Create invoice items for drugs
        if (isset($data['drug_items']) && is_array($data['drug_items'])) {
            foreach ($data['drug_items'] as $drugItem) {
                if (isset($drugItem['batch_number']) &&
                    isset($drugItem['quantity']) &&
                    !empty($drugItem['batch_number']) &&
                    $drugItem['quantity'] > 0) {

                    $batch = DrugBatch::where('batch_number', $drugItem['batch_number'])->first();

                    if ($batch) {
                        // Create invoice item
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'itemable_id' => $batch->id,
                            'itemable_type' => DrugBatch::class,
                            'quantity' => $drugItem['quantity'],
                            'unit_price' => $drugItem['unit_price'] ?? 0,
                            'line_total' => $drugItem['line_total'] ?? 0,
                        ]);

                        // Reduce stock
                        if ($batch->quantity_available >= $drugItem['quantity']) {
                            $batch->decrement('quantity_available', $drugItem['quantity']);
                        }
                    }
                }
            }
        }

        // Create invoice items for services
        if (isset($data['selected_services']) &&
            is_array($data['selected_services']) &&
            $data['invoiceable_type'] === Visit::class) {

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

        // Auto-open print window
        if (Setting::get('auto_open_print_window', true)) {
            $printUrl = app(\App\Services\PrinterService::class)->printInvoice($invoice);

            $this->js("
                setTimeout(function() {
                    window.open('{$printUrl}', '_blank', 'width=400,height=600,scrollbars=yes');
                }, 1000);
            ");
        }
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { 
                size: 80mm auto; 
                margin: 0; 
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 5px;
            width: 80mm;
            background: white;
        }
        
        .receipt {
            width: 100%;
            max-width: 80mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .clinic-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .clinic-info {
            font-size: 10px;
            margin-bottom: 1px;
        }
        
        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        
        .invoice-details {
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        .invoice-details div {
            margin-bottom: 1px;
        }
        
        .items {
            margin-bottom: 10px;
        }
        
        .item {
            margin-bottom: 2px;
            font-size: 9px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 1px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 1px;
            font-size: 9px;
        }
        
        .item-line {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }
        
        .item-compact {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1px;
            font-size: 8px;
        }
        
        .item-compact .name {
            flex: 1;
            font-weight: bold;
            margin-right: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .item-compact .details {
            white-space: nowrap;
            font-size: 8px;
        }
        
        .items-summary {
            font-size: 8px;
            text-align: center;
            margin: 5px 0;
            font-style: italic;
        }
        
        .total-section {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .total {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin: 5px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .print-controls {
            background: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 12px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .close-button {
            background: #6c757d;
        }
        
        .close-button:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="no-print print-controls">
        <button class="print-button" onclick="printReceipt()">🖨️ Print Receipt</button>
        <button class="print-button close-button" onclick="closeWindow()">❌ Close</button>
        <p style="margin: 5px 0; font-size: 11px; color: #666;">
            💡 Optimized for 80mm thermal printers
        </p>
    </div>

    <div class="receipt">
        <div class="header">
            <div class="clinic-name">{{ \App\Models\Setting::get('clinic_name', 'Medical Clinic') }}</div>
            @if(\App\Models\Setting::get('clinic_address'))
                <div class="clinic-info">{{ \App\Models\Setting::get('clinic_address') }}</div>
            @endif
            @if(\App\Models\Setting::get('clinic_phone'))
                <div class="clinic-info">Tel: {{ \App\Models\Setting::get('clinic_phone') }}</div>
            @endif
        </div>

        <div class="invoice-details">
            <div><strong>INVOICE: {{ $invoice->invoice_number }}</strong></div>
            <div>Date: {{ $invoice->invoice_date->format('d/m/Y H:i') }}</div>
            @if($invoice->invoiceable && $invoice->invoiceable->patient)
                <div>Patient: {{ $invoice->invoiceable->patient->name }}</div>
                @if($invoice->invoiceable->patient->phone)
                    <div>Phone: {{ $invoice->invoiceable->patient->phone }}</div>
                @endif
            @endif
            @if($invoice->invoiceable_type === 'App\\Models\\Visit' && $invoice->invoiceable->doctor)
                <div>Doctor: Dr. {{ $invoice->invoiceable->doctor->name }}</div>
            @endif
            <div style="font-size: 8px; margin-top: 2px;">Items: {{ $invoice->invoiceItems->count() }}</div>
        </div>

        <div class="separator"></div>

        <div class="items">
            @php
                // Group items by type
                $drugItems = $invoice->invoiceItems->filter(function($item) {
                    return $item->itemable_type === 'App\\Models\\DrugBatch';
                });
                $serviceItems = $invoice->invoiceItems->filter(function($item) {
                    return $item->itemable_type === 'App\\Models\\Service';
                });
                
                $totalItems = $invoice->invoiceItems->count();
                $maxDetailedItems = \App\Models\Setting::get('thermal_max_items', 12);
            @endphp
            
            @if($totalItems <= $maxDetailedItems)
                {{-- Show all items grouped by type --}}
                @if($drugItems->count() > 0)
                    <div style="font-weight: bold; font-size: 9px; margin: 3px 0;">MEDICINES ({{ $drugItems->count() }}):</div>
                    @foreach($drugItems as $item)
                        <div class="item">
                            <div class="item-name">{{ \Str::limit($item->itemable->drug->name ?? $item->itemable->name ?? 'Unknown Drug', 20) }}</div>
                            <div class="item-line">
                                <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0) }}</span>
                                <span>{{ number_format($item->line_total, 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                @if($serviceItems->count() > 0)
                    @if($drugItems->count() > 0)<div class="separator"></div>@endif
                    <div style="font-weight: bold; font-size: 9px; margin: 3px 0;">SERVICES ({{ $serviceItems->count() }}):</div>
                    @foreach($serviceItems as $item)
                        <div class="item">
                            <div class="item-name">{{ \Str::limit($item->itemable->name ?? 'Unknown Service', 20) }}</div>
                            <div class="item-line">
                                <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0) }}</span>
                                <span>{{ number_format($item->line_total, 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            @else
                {{-- Compact format for many items --}}
                @if($drugItems->count() > 0)
                    <div style="font-weight: bold; font-size: 9px; margin: 3px 0;">MEDICINES ({{ $drugItems->count() }}):</div>
                    @foreach($drugItems->take(8) as $item)
                        <div class="item-compact">
                            <span class="name">{{ \Str::limit($item->itemable->drug->name ?? $item->itemable->name ?? 'Unknown', 14) }}</span>
                            <span class="details">{{ $item->quantity }}x {{ number_format($item->line_total, 0) }}</span>
                        </div>
                    @endforeach
                    @if($drugItems->count() > 8)
                        <div class="items-summary">... {{ $drugItems->count() - 8 }} more medicines</div>
                        @foreach($drugItems->skip(8) as $item)
                            <div class="item-compact">
                                <span class="name">{{ \Str::limit($item->itemable->drug->name ?? $item->itemable->name ?? 'Unknown', 14) }}</span>
                                <span class="details">{{ $item->quantity }}x {{ number_format($item->line_total, 0) }}</span>
                            </div>
                        @endforeach
                    @endif
                @endif
                
                @if($serviceItems->count() > 0)
                    <div class="separator"></div>
                    <div style="font-weight: bold; font-size: 9px; margin: 3px 0;">SERVICES ({{ $serviceItems->count() }}):</div>
                    @foreach($serviceItems as $item)
                        <div class="item-compact">
                            <span class="name">{{ \Str::limit($item->itemable->name ?? 'Unknown', 14) }}</span>
                            <span class="details">{{ $item->quantity }}x {{ number_format($item->line_total, 0) }}</span>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>

        <div class="total-section">
            <div class="total">
                TOTAL: {{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($invoice->total_amount, 0) }}
            </div>
        </div>

        <div class="footer">
            <div>Thank you for your visit!</div>
            @if($invoice->notes)
                <div style="margin-top: 5px; font-size: 8px;">
                    {{ \Str::limit($invoice->notes, 50) }}
                </div>
            @endif
            <div style="margin-top: 5px;">
                {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads if setting is enabled
        window.onload = function() {
            const autoPrint = {{ \App\Models\Setting::get('auto_open_print_window', true) ? 'true' : 'false' }};
            if (autoPrint) {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        };
        
        // Handle print button click
        function printReceipt() {
            window.print();
        }
        
        // Handle close button click
        function closeWindow() {
            window.close();
        }
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            if (confirm('Print completed. Close this window?')) {
                window.close();
            }
        };
    </script>
</body>
</html>
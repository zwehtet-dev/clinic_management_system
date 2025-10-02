<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            max-width: 300px;
        }
        
        .receipt {
            border: 1px solid #ccc;
            padding: 10px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .separator {
            border-top: 1px solid #000;
            margin: 10px 0;
        }
        
        .invoice-details {
            margin-bottom: 15px;
        }
        
        .items {
            margin-bottom: 15px;
        }
        
        .item {
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .item-line {
            display: flex;
            justify-content: space-between;
        }
        
        .item-compact {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }
        
        .item-compact .name {
            flex: 1;
            margin-right: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .items-summary {
            text-align: center;
            font-style: italic;
            margin: 10px 0;
            font-size: 10px;
            color: #666;
        }
        
        .total {
            font-weight: bold;
            font-size: 14px;
            text-align: right;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-button" onclick="printInvoice()">🖨️ Print Invoice</button>
        <button class="print-button" onclick="closeWindow()" style="background: #6c757d;">❌ Close</button>
        <p style="margin: 10px 0; font-size: 12px; color: #666;">
            💡 Tip: Make sure your printer is connected and ready. Use Ctrl+P (Cmd+P on Mac) to print.
        </p>
    </div>

    <div class="receipt">
        <div class="header">
            <div class="clinic-name">{{ \App\Models\Setting::get('clinic_name', 'Medical Clinic') }}</div>
            <div>{{ \App\Models\Setting::get('clinic_address', '') }}</div>
            <div>Tel: {{ \App\Models\Setting::get('clinic_phone', '') }}</div>
        </div>

        <div class="separator"></div>

        @php
            $isVisitInvoice = $invoice->invoiceable_type === 'App\\Models\\Visit';
            $isDrugSaleInvoice = $invoice->invoiceable_type === 'App\\Models\\DrugSale';
        @endphp

        <div class="invoice-details">
            <div><strong>
                @if($isVisitInvoice)
                    CONSULTATION INVOICE: {{ $invoice->invoice_number }}
                @elseif($isDrugSaleInvoice)
                    PHARMACY RECEIPT: {{ $invoice->invoice_number }}
                @else
                    INVOICE: {{ $invoice->invoice_number }}
                @endif
            </strong></div>
            <div>Date: {{ $invoice->invoice_date->format('M d, Y H:i') }}</div>
            @if($invoice->invoiceable && $invoice->invoiceable->patient)
                <div>Patient: {{ $invoice->invoiceable->patient->name }}</div>
                @if($invoice->invoiceable->patient->phone)
                    <div>Phone: {{ $invoice->invoiceable->patient->phone }}</div>
                @endif
            @elseif($isDrugSaleInvoice && $invoice->invoiceable->buyer_name)
                <div>Customer: {{ $invoice->invoiceable->buyer_name }}</div>
            @elseif($isDrugSaleInvoice)
                <div>Customer: Walk-in Customer</div>
            @endif
            @if($isVisitInvoice && $invoice->invoiceable->doctor)
                <div>Doctor: Dr. {{ $invoice->invoiceable->doctor->name }}</div>
                <div>Visit: {{ $invoice->invoiceable->public_id }}</div>
            @endif
        </div>

        <div class="separator"></div>

        <div class="items">
            @php
                $itemCount = $invoice->invoiceItems->count();
                $maxDetailedItems = \App\Models\Setting::get('receipt_max_items', 15);
            @endphp

            {{-- Consultation is now included as a service item --}}
            
            @if($itemCount <= $maxDetailedItems)
                {{-- Show all items in detail if 12 or fewer --}}
                @foreach($invoice->invoiceItems as $item)
                    <div class="item">
                        <div>{{ \Str::limit($item->itemable_type === 'App\\Models\\DrugBatch' ? ($item->itemable->drug->name ?? 'Unknown Drug') : ($item->itemable->name ?? 'Unknown Item'), 28) }}</div>
                        <div class="item-line">
                            <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                            <span>{{ number_format($item->line_total, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Show first 8 items in detail --}}
                @foreach($invoice->invoiceItems->take(8) as $item)
                    <div class="item">
                        <div>{{ \Str::limit($item->itemable_type === 'App\\Models\\DrugBatch' ? ($item->itemable->drug->name ?? 'Unknown Drug') : ($item->itemable->name ?? 'Unknown Item'), 28) }}</div>
                        <div class="item-line">
                            <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                            <span>{{ number_format($item->line_total, 2) }}</span>
                        </div>
                    </div>
                @endforeach
                
                <div class="items-summary">
                    ... {{ $itemCount - 8 }} more items ...
                </div>
                
                {{-- Show remaining items in compact format --}}
                @foreach($invoice->invoiceItems->skip(8) as $item)
                    <div class="item-compact">
                        <span class="name">{{ \Str::limit($item->itemable_type === 'App\\Models\\DrugBatch' ? ($item->itemable->drug->name ?? 'Unknown Drug') : ($item->itemable->name ?? 'Unknown'), 18) }}</span>
                        <span>{{ $item->quantity }}x {{ number_format($item->line_total, 0) }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="separator"></div>

        <div class="total">
            TOTAL: {{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($invoice->total_amount, 2) }}
        </div>

        <div class="separator"></div>

        <div class="footer">
            <div>Thank you for your visit!</div>
            @if($invoice->notes)
                <div style="margin-top: 10px;">
                    <strong>Notes:</strong><br>
                    {{ $invoice->notes }}
                </div>
            @endif
            <div style="margin-top: 10px;">
                Printed: {{ now()->format('M d, Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads if setting is enabled
        window.onload = function() {
            // Check if auto-print is enabled (you can pass this from the controller)
            const autoPrint = {{ \App\Models\Setting::get('auto_open_print_window', true) ? 'true' : 'false' }};
            if (autoPrint) {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        };
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            if (confirm('Print completed. Close this window?')) {
                window.close();
            }
        };
        
        // Handle print button click
        function printInvoice() {
            window.print();
        }
        
        // Handle close button click
        function closeWindow() {
            window.close();
        }
    </script>
</body>
</html>
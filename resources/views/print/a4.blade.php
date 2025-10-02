<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { 
                size: A4; 
                margin: 15mm; 
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #333;
        }
        
        .invoice {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .clinic-info {
            flex: 1;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .clinic-details {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-title {
            text-align: right;
            flex: 1;
        }
        
        .invoice-number {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 11px;
            color: #666;
        }
        
        .patient-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .patient-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #007bff;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 100px;
            color: #555;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: #007bff;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .items-table .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .items-table .item-details {
            font-size: 9px;
            color: #666;
        }
        
        .items-summary {
            background: #e9ecef;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
            font-size: 11px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .total-row.final {
            font-size: 16px;
            font-weight: bold;
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 10px;
        }
        
        .notes {
            clear: both;
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .print-controls {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 10px;
            font-size: 14px;
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
        <button class="print-button" onclick="printInvoice()">🖨️ Print Invoice</button>
        <button class="print-button close-button" onclick="closeWindow()">❌ Close</button>
        <p style="margin: 10px 0; color: #666;">
            💡 Optimized for A4 paper printing
        </p>
    </div>

    <div class="invoice">
        <div class="header">
            <div class="clinic-info">
                <div class="clinic-name">{{ \App\Models\Setting::get('clinic_name', 'Medical Clinic') }}</div>
                <div class="clinic-details">
                    @if(\App\Models\Setting::get('clinic_address'))
                        <div>{{ \App\Models\Setting::get('clinic_address') }}</div>
                    @endif
                    @if(\App\Models\Setting::get('clinic_phone'))
                        <div>Phone: {{ \App\Models\Setting::get('clinic_phone') }}</div>
                    @endif
                    @if(\App\Models\Setting::get('clinic_email'))
                        <div>Email: {{ \App\Models\Setting::get('clinic_email') }}</div>
                    @endif
                </div>
            </div>
            <div class="invoice-title">
                <div class="invoice-number">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                <div class="invoice-date">Date: {{ $invoice->invoice_date->format('F d, Y') }}</div>
            </div>
        </div>

        @php
            $isVisitInvoice = $invoice->invoiceable_type === 'App\\Models\\Visit';
            $isDrugSaleInvoice = $invoice->invoiceable_type === 'App\\Models\\DrugSale';
        @endphp

        @if($invoice->invoiceable && ($invoice->invoiceable->patient || $isDrugSaleInvoice))
        <div class="patient-info">
            <h3>
                @if($isVisitInvoice)
                    Medical Consultation Invoice
                @elseif($isDrugSaleInvoice)
                    Pharmacy Sale Invoice
                @else
                    Patient Information
                @endif
            </h3>
            @if($invoice->invoiceable->patient)
            <div class="info-row">
                <span class="info-label">Patient:</span>
                <span>{{ $invoice->invoiceable->patient->name }}</span>
            </div>
            @if($invoice->invoiceable->patient->phone)
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span>{{ $invoice->invoiceable->patient->phone }}</span>
            </div>
            @endif
            @if($invoice->invoiceable->patient->age)
            <div class="info-row">
                <span class="info-label">Age:</span>
                <span>{{ $invoice->invoiceable->patient->age }} years</span>
            </div>
            @endif
            @elseif($isDrugSaleInvoice && $invoice->invoiceable->buyer_name)
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span>{{ $invoice->invoiceable->buyer_name }}</span>
            </div>
            @elseif($isDrugSaleInvoice)
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span>Walk-in Customer</span>
            </div>
            @endif
            @if($isVisitInvoice && $invoice->invoiceable->doctor)
            <div class="info-row">
                <span class="info-label">Doctor:</span>
                <span>Dr. {{ $invoice->invoiceable->doctor->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Specialization:</span>
                <span>{{ $invoice->invoiceable->doctor->specialization ?? 'General Practice' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Visit ID:</span>
                <span>{{ $invoice->invoiceable->public_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Visit Date:</span>
                <span>{{ $invoice->invoiceable->visit_date->format('F d, Y \a\t H:i') }}</span>
            </div>
            @endif
            @if($isDrugSaleInvoice)
            <div class="info-row">
                <span class="info-label">Sale ID:</span>
                <span>{{ $invoice->invoiceable->public_id ?? 'DS-' . $invoice->invoiceable->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sale Date:</span>
                <span>{{ ($invoice->invoiceable->sale_date ?? $invoice->invoiceable->created_at)->format('F d, Y \a\t H:i') }}</span>
            </div>
            @if($invoice->invoiceable->buyer_name && !$invoice->invoiceable->patient)
            <div class="info-row">
                <span class="info-label">Buyer:</span>
                <span>{{ $invoice->invoiceable->buyer_name }}</span>
            </div>
            @endif
            @endif
        </div>
        @endif

        @php
            // Group items by type for better organization
            $drugItems = $invoice->invoiceItems->filter(function($item) {
                return $item->itemable_type === 'App\\Models\\DrugBatch';
            });
            $serviceItems = $invoice->invoiceItems->filter(function($item) {
                return $item->itemable_type === 'App\\Models\\Service';
            });
            
            // Consultation fee is now handled as a service item, no separate calculation needed
            
            $itemsPerPage = 30; // Items per page for A4
            $totalItems = $invoice->invoiceItems->count();
            
            // Combine items with section headers
            $organizedItems = collect();
            if($serviceItems->count() > 0) {
                $organizedItems = $organizedItems->merge($serviceItems);
            }
            if($drugItems->count() > 0) {
                $organizedItems = $organizedItems->merge($drugItems);
            }
            
            $itemChunks = $organizedItems->chunk($itemsPerPage);
        @endphp

        @foreach($itemChunks as $chunkIndex => $itemChunk)
            @if($chunkIndex > 0)
                <div class="page-break"></div>
                {{-- Repeat header on new pages --}}
                <div class="header">
                    <div class="clinic-info">
                        <div class="clinic-name">{{ \App\Models\Setting::get('clinic_name', 'Medical Clinic') }}</div>
                    </div>
                    <div class="invoice-title">
                        <div class="invoice-number">INVOICE (Continued)</div>
                        <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                        <div class="invoice-date">Page {{ $chunkIndex + 1 }}</div>
                    </div>
                </div>
            @endif

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Description</th>
                        <th style="width: 15%" class="text-center">Qty</th>
                        <th style="width: 20%" class="text-right">Unit Price</th>
                        <th style="width: 15%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentSection = '';
                        $itemNumber = ($chunkIndex * $itemsPerPage) + 1;
                    @endphp
                    
                    {{-- Consultation is now included as a service item, no separate handling needed --}}

                    @foreach($itemChunk as $index => $item)
                        @php
                            $newSection = '';
                            if($item->itemable_type === 'App\\Models\\DrugBatch') {
                                $newSection = $isVisitInvoice ? 'PRESCRIBED MEDICINES' : 'MEDICINES & DRUGS';
                            } elseif($item->itemable_type === 'App\\Models\\Service') {
                                $newSection = $isVisitInvoice ? 'ADDITIONAL SERVICES' : 'MEDICAL SERVICES';
                            }
                        @endphp
                        
                        @if($newSection !== $currentSection && $chunkIndex === 0)
                            <tr style="background: #e9ecef;">
                                <td colspan="5" style="font-weight: bold; padding: 8px; font-size: 11px;">
                                    {{ $newSection }}
                                    @if($newSection === 'PRESCRIBED MEDICINES' || $newSection === 'MEDICINES & DRUGS')
                                        ({{ $drugItems->count() }} items)
                                    @elseif($newSection === 'ADDITIONAL SERVICES' || $newSection === 'MEDICAL SERVICES')
                                        ({{ $serviceItems->count() }} items)
                                    @endif
                                </td>
                            </tr>
                            @php $currentSection = $newSection; @endphp
                        @endif
                        
                        <tr>
                            <td class="text-center">{{ $itemNumber++ }}</td>
                            <td>
                                <div class="item-name">
                                    @if($item->itemable_type === 'App\\Models\\DrugBatch')
                                        {{ $item->itemable->drug->name ?? $item->itemable->name ?? 'Unknown Drug' }}
                                    @else
                                        {{ $item->itemable->name ?? 'Unknown Item' }}
                                    @endif
                                </div>
                                @if($item->itemable_type === 'App\\Models\\DrugBatch' && $item->itemable->batch_number)
                                    <div class="item-details">Batch: {{ $item->itemable->batch_number }}</div>
                                @endif
                                @if($item->itemable_type === 'App\\Models\\Service' && $item->itemable->description)
                                    <div class="item-details">{{ \Str::limit($item->itemable->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($chunkIndex === 0)
                <div class="items-summary">
                    <strong>Invoice Summary:</strong> 
                    @if($isVisitInvoice)
                        {{ $drugItems->count() }} Prescribed Medicine(s) | 
                        {{ $serviceItems->count() }} Medical Service(s) | 
                        <strong>Total {{ $totalItems }} Items</strong>
                    @else
                        {{ $drugItems->count() }} Medicine(s) | 
                        {{ $serviceItems->count() }} Service(s) | 
                        <strong>Total {{ $totalItems }} Items</strong>
                    @endif
                    @if($itemChunks->count() > 1)
                        | Continued on {{ $itemChunks->count() - 1 }} more page(s)
                    @endif
                </div>
            @endif
        @endforeach

        <div class="total-section">
            <div class="total-row">
                <span>{{ $isVisitInvoice ? 'Services & Medicines:' : 'Subtotal:' }}</span>
                <span>{{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($invoice->invoiceItems->sum('line_total'), 2) }}</span>
            </div>
            @if(\App\Models\Setting::get('tax_rate', 0) > 0)
            <div class="total-row">
                <span>Tax ({{ \App\Models\Setting::get('tax_rate') }}%):</span>
                <span>{{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($invoice->total_amount * \App\Models\Setting::get('tax_rate', 0) / 100, 2) }}</span>
            </div>
            @endif
            <div class="total-row final">
                <span>TOTAL AMOUNT:</span>
                <span>{{ \App\Models\Setting::get('currency_symbol', 'Ks') }} {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        @if($invoice->notes)
        <div class="notes">
            <h4 style="margin: 0 0 10px 0;">Notes:</h4>
            <p style="margin: 0;">{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="footer">
            <p>{{ \App\Models\Setting::get('invoice_terms', 'Payment due upon receipt') }}</p>
            <p>Printed on {{ now()->format('F d, Y \a\t H:i') }}</p>
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
        function printInvoice() {
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
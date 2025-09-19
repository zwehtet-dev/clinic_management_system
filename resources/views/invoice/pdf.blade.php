<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .clinic-info {
            color: #666;
            margin-bottom: 5px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-details, .patient-details {
            width: 45%;
        }
        .invoice-details h3, .patient-details h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
        }
        .detail-row {
            margin-bottom: 5px;
        }
        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table .qty, .items-table .price, .items-table .total {
            text-align: right;
            width: 80px;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-row {
            margin-bottom: 10px;
        }
        .total-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        .total-amount {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #007cba;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-name">{{ config('app.clinic_name', 'Medical Clinic') }}</div>
        <div class="clinic-info">{{ config('app.clinic_address', '') }}</div>
        <div class="clinic-info">Tel: {{ config('app.clinic_phone', '') }}</div>
        <div class="clinic-info">Email: {{ config('app.clinic_email', '') }}</div>
    </div>

    <div class="invoice-info">
        <div class="invoice-details">
            <h3>Invoice Details</h3>
            <div class="detail-row">
                <span class="detail-label">Invoice #:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span>{{ $invoice->invoice_date->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span>{{ $invoice->created_at->format('H:i') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span>{{ ucfirst($invoice->status) }}</span>
            </div>
            @if($invoice->invoiceable_type === 'App\\Models\\Visit')
                <div class="detail-row">
                    <span class="detail-label">Visit ID:</span>
                    <span>{{ $invoice->invoiceable->public_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Doctor:</span>
                    <span>Dr. {{ $invoice->invoiceable->doctor->name }}</span>
                </div>
            @endif
        </div>

        <div class="patient-details">
            <h3>Patient Details</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span>{{ $invoice->patient->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Patient ID:</span>
                <span>{{ $invoice->patient->public_id }}</span>
            </div>
            @if($invoice->patient->phone)
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span>{{ $invoice->patient->phone }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Age:</span>
                <span>{{ $invoice->patient->age }} years</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Gender:</span>
                <span>{{ ucfirst($invoice->patient->gender) }}</span>
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="qty">Qty</th>
                <th class="price">Unit Price</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->invoiceItems as $item)
            <tr>
                <td>
                    <strong>{{ $item->itemable->name }}</strong>
                    @if($item->itemable_type === 'App\\Models\\Drug')
                        <br><small>{{ $item->itemable->strength }}{{ $item->itemable->unit }} - {{ $item->itemable->drugForm->name }}</small>
                    @endif
                </td>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="price">${{ number_format($item->unit_price, 2) }}</td>
                <td class="total">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-amount">${{ number_format($invoice->invoiceItems->sum('line_total'), 2) }}</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">Total Amount:</span>
            <span class="total-amount">${{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <div class="notes">
        <h4 style="margin-top: 0;">Notes:</h4>
        <p style="margin-bottom: 0;">{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing our clinic!</p>
        <p>This invoice was generated on {{ now()->format('M d, Y \a\t H:i') }}</p>
    </div>
</body>
</html>

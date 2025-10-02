<?php

namespace App\Exports;

use App\Models\DrugBatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class DrugBatchesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return DrugBatch::with('drug')->get();
    }

    public function headings(): array
    {
        return [
            'Drug Public ID',
            'Drug Name',
            'Batch Number',
            'Purchase Price',
            'Sell Price',
            'Quantity Received',
            'Quantity Available',
            'Expiry Date',
            'Received Date',
            'Created At',
            'Updated At'
        ];
    }

    public function map($drugBatch): array
    {
        return [
            $drugBatch->drug->public_id ?? 'N/A',
            $drugBatch->drug->name ?? 'N/A',
            $drugBatch->batch_number,
            $drugBatch->purchase_price,
            $drugBatch->sell_price,
            $drugBatch->quantity_received,
            $drugBatch->quantity_available,
            $drugBatch->expiry_date?->format('Y-m-d'),
            $drugBatch->received_date?->format('Y-m-d'),
            $drugBatch->created_at?->format('Y-m-d H:i:s'),
            $drugBatch->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
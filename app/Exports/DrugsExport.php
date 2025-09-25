<?php

namespace App\Exports;

use App\Models\Drug;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrugsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Drug::with('drugForm')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Catelog',
            'Generic Name',
            'Drug Form',
            'Strength',
            'Unit',
            'Min Stock',
            'Expire Alert (Days)',
            'Description',
            'Is Active',
            'Total Stock',
            'Created At',
            'Updated At',
        ];
    }

    public function map($drug): array
    {
        return [
            $drug->id,
            $drug->name,
            $drug->catelog,
            $drug->generic_name,
            $drug->drugForm?->name,
            $drug->strength,
            $drug->unit,
            $drug->min_stock,
            $drug->expire_alert,
            $drug->description,
            $drug->is_active ? 'Yes' : 'No',
            $drug->total_stock,
            $drug->created_at?->format('Y-m-d H:i:s'),
            $drug->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
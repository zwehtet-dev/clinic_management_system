<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ServicesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Service::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Description',
            'Price',
            'Is Active',
            'Created At',
            'Updated At'
        ];
    }

    public function map($service): array
    {
        return [
            $service->name,
            $service->description,
            $service->price,
            $service->is_active ? 'Yes' : 'No',
            $service->created_at?->format('Y-m-d H:i:s'),
            $service->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
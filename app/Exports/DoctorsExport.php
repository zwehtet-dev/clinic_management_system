<?php

namespace App\Exports;

use App\Models\Doctor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class DoctorsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return Doctor::all();
    }

    public function headings(): array
    {
        return [
            'Public ID',
            'Name',
            'Specialization',
            'License Number',
            'Phone',
            'Address',
            'Notes',
            'Is Active',
            'Created At',
            'Updated At'
        ];
    }

    public function map($doctor): array
    {
        return [
            $doctor->public_id,
            $doctor->name,
            $doctor->specialization,
            $doctor->license_number,
            $doctor->phone,
            $doctor->address,
            $doctor->notes,
            $doctor->is_active ? 'Yes' : 'No',
            $doctor->created_at?->format('Y-m-d H:i:s'),
            $doctor->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
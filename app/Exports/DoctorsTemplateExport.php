<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    use Exportable;

    public function array(): array
    {
        // Return sample data to help users understand the format
        return [
            [
                'DOC001',
                'Dr. John Smith',
                'Cardiology',
                'LIC123456',
                '+1234567890',
                '123 Medical Street, City',
                'Experienced cardiologist',
                true
            ],
            [
                'DOC002',
                'Dr. Jane Doe',
                'Pediatrics',
                'LIC789012',
                '+0987654321',
                '456 Health Avenue, City',
                'Specialist in child healthcare',
                true
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'public_id',
            'name',
            'specialization',
            'license_number',
            'phone',
            'address',
            'notes',
            'is_active'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']]],
            // Add some sample styling
            2 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F2F2F2']]],
            3 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F2F2F2']]],
        ];
    }
}
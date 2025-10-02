<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    use Exportable;

    public function array(): array
    {
        // Return sample data to help users understand the format
        return [
            [
                'General Consultation',
                'Basic medical consultation and examination',
                50.00,
                true
            ],
            [
                'Blood Test',
                'Complete blood count and analysis',
                25.00,
                true
            ],
            [
                'X-Ray',
                'Digital X-ray imaging service',
                75.00,
                true
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'price',
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
            4 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F2F2F2']]],
        ];
    }
}
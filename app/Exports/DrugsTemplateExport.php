<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrugsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    use Exportable;

    public function array(): array
    {
        // Return sample data to help users understand the format
        return [
            [
                'Paracetamol 500mg',
                'PARA-500',
                'Paracetamol',
                'Tablet',
                '500mg',
                'mg',
                10,
                30,
                'Pain reliever and fever reducer',
                true
            ],
            [
                'Amoxicillin 250mg',
                'AMOX-250',
                'Amoxicillin',
                'Capsule',
                '250mg',
                'mg',
                5,
                30,
                'Antibiotic for bacterial infections',
                true
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'catelog',
            'generic_name',
            'drug_form',
            'strength',
            'unit',
            'min_stock',
            'expire_alert',
            'description',
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
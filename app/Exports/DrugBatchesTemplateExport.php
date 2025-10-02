<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrugBatchesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    use Exportable;

    public function array(): array
    {
        // Return sample data to help users understand the format
        // Note: batch_number can be left empty for auto-generation
        // Dates should be in Y-m-d format: 2025-10-25, 2025-12-31, etc.
        return [
            [
                'DRU-2025-000001',
                'Paracetamol 500mg',
                '', // Empty batch_number - will auto-generate as BAT-1-000001
                15.50,
                25.00,
                100,
                100,
                '2025-12-31', // Y-m-d format
                '2024-01-15'  // Y-m-d format
            ],
            [
                'DRU-2025-000002',
                'Amoxicillin 250mg',
                'CUSTOM-BATCH-001', // Custom batch number - will be used as provided
                8.75,
                15.00,
                50,
                50,
                '2026-06-30', // Y-m-d format
                '2024-01-15'  // Y-m-d format
            ],
            [
                'DRU-2025-000001',
                'Paracetamol 500mg',
                '', // Empty batch_number - will auto-generate as BAT-1-000002
                12.00,
                20.00,
                200,
                200,
                '2025-11-30', // Y-m-d format
                '2024-01-20'  // Y-m-d format
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'drug_public_id',
            'drug_name',
            'batch_number (optional - auto-generated if empty)',
            'purchase_price',
            'sell_price',
            'quantity_received',
            'quantity_available',
            'expiry_date (Y-m-d format: 2025-10-25)',
            'received_date (Y-m-d format: 2025-10-25)'
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
<?php

namespace App\Imports;

/**
 * DrugBatchesImport
 *
 * This import class supports importing drug batches using either:
 * 1. drug_public_id (preferred) - The unique public ID of the drug (e.g., DRU-2025-000001)
 * 2. drug_name (backward compatibility) - The name or catalog of the drug
 *
 * The import will first try to find the drug by public_id, then fallback to drug_name.
 * At least one of these fields must be provided for each row.
 *
 * Batch Number Generation:
 * - If batch_number is provided, it will be used
 * - If batch_number is empty, it will be auto-generated using format: BAT-{drug_id}-000001
 */

use App\Models\Drug;
use App\Models\DrugBatch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DrugBatchesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find drug by public_id first, then fallback to name or catelog for backward compatibility
        $drug = null;

        if (!empty($row['drug_public_id'])) {
            $drug = Drug::where('public_id', $row['drug_public_id'])->first();
        }

        // Fallback to drug_name for backward compatibility
        if (!$drug && !empty($row['drug_name'])) {
            $drug = Drug::where('name', $row['drug_name'])
                ->orWhere('catelog', $row['drug_name'])
                ->first();
        }

        if (!$drug) {
            return null; // Skip if drug not found
        }

        return new DrugBatch([
            'drug_id' => $drug->id,
            'batch_number' => !empty($row['batch_number']) ? $row['batch_number'] : null, // Let model auto-generate if empty
            'purchase_price' => $row['purchase_price'] ?? 0,
            'sell_price' => $row['sell_price'] ?? 0,
            'quantity_received' => $row['quantity_received'] ?? 0,
            'quantity_available' => $row['quantity_available'] ?? $row['quantity_received'] ?? 0,
            'expiry_date' => $this->parseDate($row['expiry_date'] ?? null),
            'received_date' => $this->parseDate($row['received_date'] ?? null) ?? now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'drug_public_id' => 'nullable|string|max:255|exists:drugs,public_id',
            'drug_name' => 'nullable|string|max:255', // Keep for backward compatibility
            'batch_number' => 'nullable|string|max:255', // Made optional - will auto-generate if empty
            'purchase_price' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'quantity_received' => 'required|integer|min:1',
            'quantity_available' => 'nullable|integer|min:0',
            'expiry_date' => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->parseDate($value)) {
                    $fail("The {$attribute} field must be a valid date (e.g., 2025-10-25, 10/25/2025).");
                }
            }],
            'received_date' => ['nullable', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->parseDate($value)) {
                    $fail("The {$attribute} field must be a valid date (e.g., 2025-10-25, 10/25/2025).");
                }
            }],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'drug_public_id.exists' => 'The drug with this public ID does not exist.',
        ];
    }

    public function prepareForValidation($data, $index)
    {
        // Ensure at least one drug identifier is provided
        if (empty($data['drug_public_id']) && empty($data['drug_name'])) {
            throw new \Exception("Either drug_public_id or drug_name must be provided at row {$index}");
        }

        return $data;
    }

    /**
     * Parse date from various formats including Excel serial dates
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // If it's already a Carbon instance or DateTime, return as is
        if ($dateValue instanceof Carbon || $dateValue instanceof \DateTime) {
            return $dateValue;
        }

        // Handle Excel serial date numbers
        if (is_numeric($dateValue)) {
            try {
                // Check if it's a valid Excel date serial number (between 1 and 2958465 for valid Excel dates)
                $numericValue = (float) $dateValue;
                if ($numericValue >= 1 && $numericValue <= 2958465) {
                    return Carbon::instance(Date::excelToDateTimeObject($numericValue));
                }
            } catch (\Exception $e) {
                // Continue to string parsing
            }
        }

        // Convert to string if it's not already
        $dateString = trim((string) $dateValue);

        // Skip empty strings
        if (empty($dateString)) {
            return null;
        }

        // First, try the exact format we expect: Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $dateString);
            } catch (\Exception $e) {
                // Continue to other formats
            }
        }

        // Try different date formats
        $formats = [
            'Y-m-d',     // 2025-10-25 (ISO format) - prioritize this format
            'n/j/Y',     // 9/25/2025 (single digit month/day)
            'm/d/Y',     // 09/25/2025 (double digit month/day)
            'd/m/Y',     // 25/09/2025 (European format)
            'm-d-Y',     // 09-25-2025 (US format with dashes)
            'd-m-Y',     // 25-09-2025 (European format with dashes)
            'Y/m/d',     // 2025/10/25
            'd.m.Y',     // 25.10.2025 (German format)
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date && $date->format($format) === $dateString) {
                    return $date;
                }
            } catch (\Exception $e) {
                // Continue to next format
                continue;
            }
        }

        // If no format worked, try Carbon's parse method as fallback
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format date for Laravel validation
     */
    private function formatDateForValidation($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        $parsedDate = $this->parseDate($dateValue);

        if ($parsedDate) {
            return $parsedDate->format('Y-m-d');
        }

        // If parsing failed, check if it's already in Y-m-d format
        $dateString = trim((string) $dateValue);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }

        // Return original value for validation to catch the error
        return $dateValue;
    }
}

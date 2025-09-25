<?php

namespace App\Imports;

use App\Models\Drug;
use App\Models\DrugForm;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class DrugsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find or create drug form
        $drugForm = null;
        if (!empty($row['drug_form'])) {
            $drugForm = DrugForm::firstOrCreate(
                ['name' => $row['drug_form']],
                ['description' => $row['drug_form'], 'is_active' => true]
            );
        }

        return new Drug([
            'name' => $row['name'],
            'catelog' => $row['catelog'] ?? null,
            'generic_name' => $row['generic_name'] ?? null,
            'drug_form_id' => $drugForm?->id,
            'strength' => $row['strength'] ?? null,
            'unit' => $row['unit'] ?? null,
            'min_stock' => $row['min_stock'] ?? 0,
            'expire_alert' => $row['expire_alert'] ?? 30,
            'description' => $row['description'] ?? null,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'catelog' => 'nullable|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'drug_form' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'expire_alert' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
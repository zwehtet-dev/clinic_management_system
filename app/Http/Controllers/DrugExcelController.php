<?php

namespace App\Http\Controllers;

use App\Exports\DrugsExport;
use App\Exports\DrugsTemplateExport;
use App\Imports\DrugsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DrugExcelController extends Controller
{
    public function export()
    {
        return Excel::download(new DrugsExport, 'drugs_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new DrugsTemplateExport, 'drugs_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $import = new DrugsImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors();

            if (count($failures) > 0 || count($errors) > 0) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Import completed with errors',
                    'errors' => $errorMessages
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Drugs imported successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
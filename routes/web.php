<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Print routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/print/invoice/{invoice}', [App\Http\Controllers\PrintController::class, 'printInvoice'])->name('print.invoice');
    Route::post('/print/thermal/{invoice}', [App\Http\Controllers\PrintController::class, 'printThermal'])->name('print.thermal');
    Route::post('/print/a4/{invoice}', [App\Http\Controllers\PrintController::class, 'printA4'])->name('print.a4');
});

// Drug Excel routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/drugs/export', [App\Http\Controllers\DrugExcelController::class, 'export'])->name('drugs.export');
    Route::get('/drugs/template', [App\Http\Controllers\DrugExcelController::class, 'template'])->name('drugs.template');
    Route::post('/drugs/import', [App\Http\Controllers\DrugExcelController::class, 'import'])->name('drugs.import');
    Route::get('/drugs/import-page', function () {
        return view('drugs.import');
    })->name('drugs.import.page');
});

// Web-based printing (no auth required for print tokens)
Route::get('/print/{token}', [App\Http\Controllers\PrintController::class, 'webPrint'])->name('print.web');

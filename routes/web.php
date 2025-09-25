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

// Web-based printing (no auth required for print tokens)
Route::get('/print/{token}', [App\Http\Controllers\PrintController::class, 'webPrint'])->name('print.web');

<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::prefix('print')->middleware(['web', 'auth'])->group(function () {
    Route::post('/invoice/{invoice}', [App\Http\Controllers\PrintController::class, 'printInvoice'])->name('print.invoice');
    Route::post('/test', [App\Http\Controllers\PrintController::class, 'testPrinter'])->name('print.test');
    Route::get('/printers', [App\Http\Controllers\PrintController::class, 'getAvailablePrinters'])->name('print.printers');
    Route::post('/bulk', [App\Http\Controllers\PrintController::class, 'printMultipleInvoices'])->name('print.bulk');
});

<?php

use App\Http\Controllers\ContractPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Contract PDF routes
Route::middleware(['auth'])->group(function () {
    Route::get('/contracts/{contract}/pdf', [ContractPdfController::class, 'downloadSingle'])->name('contracts.pdf.single');
    Route::get('/contracts/pdf/report', [ContractPdfController::class, 'downloadAll'])->name('contracts.pdf.all');
    Route::get('/contracts/pdf/bulk', [ContractPdfController::class, 'downloadBulk'])->name('contracts.pdf.bulk');
});

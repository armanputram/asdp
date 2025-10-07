<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OperasionalPdfController;

// Route::get('/laporan/operasional/pdf/{id}', [OperasionalPdfController::class, 'exportPdf'])
//  ->name('laporan.operasional.pdf');

// // Route baru untuk mengekspor dokumen yang digabungkan
// Route::get('/laporan/grouped/pdf/{pelabuhanId}/{cabangId}/{userId}/{tanggal}', [OperasionalPdfController::class, 'exportGroupedPdf'])
//  ->name('laporan.grouped.pdf');

 Route::get('/export-pdf/{id}', [OperasionalPdfController::class, 'exportPdf'])->name('export.pdf');

Route::get('/', function () {
    return redirect('/admin');
});

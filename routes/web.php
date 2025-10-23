<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OperasionalPdfController;

/*
|--------------------------------------------------------------------------
| Web Routes - PDF Export Routes
|--------------------------------------------------------------------------
*/

// Route utama: Export PDF berdasarkan ID operasional
Route::get('/export-pdf/{id}', [OperasionalPdfController::class, 'exportPdf'])
    ->name('export.pdf');

// Route: Export PDF berdasarkan Pelabuhan
Route::get('/export-pdf-pelabuhan/{pelabuhanId}', [OperasionalPdfController::class, 'exportPdfByPelabuhan'])
    ->name('export.pdf.pelabuhan');

// Route: Export PDF dengan tanggal custom
Route::get('/export-pdf-custom/{id}/{tanggal?}/{waktu?}', [OperasionalPdfController::class, 'exportPdfWithCustomData'])
    ->name('export.pdf.custom');

// Route: Download PDF (alternatif dari stream)
Route::get('/download-pdf/{id}', [OperasionalPdfController::class, 'downloadPdf'])
    ->name('download.pdf');

/*
|--------------------------------------------------------------------------
| Homepage Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/admin');
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OperasionalPdfController;

Route::get('/laporan/operasional/pdf/{id}', [OperasionalPdfController::class, 'exportPdf'])
    ->name('laporan.operasional.pdf');


Route::get('/', function () {
    return view('welcome');
});

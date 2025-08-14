<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OperasionalReportController;

Route::get('/laporan-operasional/pdf', [OperasionalReportController::class, 'exportPdf'])
    ->name('laporan.operasional.pdf');


Route::get('/', function () {
    return view('welcome');
});

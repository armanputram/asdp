<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use Barryvdh\DomPDF\Facade\Pdf;

class OperasionalReportController extends Controller
{
    public function exportPdf()
    {
        $data = Operasional::with(['cabang', 'pelabuhan', 'layanan', 'items.perangkat'])->get();

        $pdf = Pdf::loadView('reports.operasional', compact('data'))
            ->setPaper('a4', 'landscape'); // ⬅ ini biar landscape

        return $pdf->stream('laporan-operasional.pdf');
        // bisa pakai ->download('laporan-operasional.pdf') kalau mau langsung download
    }
}

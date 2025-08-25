<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use Barryvdh\DomPDF\Facade\Pdf;

class OperasionalPdfController extends Controller
{
    public function exportPdf($id)
    {
        $operasional = Operasional::with([
            'cabang',
            'pelabuhan',
            'layanan',
            'items.perangkat',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.operasional', compact('operasional'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("laporan-operasional-{$id}.pdf");
    }
}

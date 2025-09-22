<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Pelabuhan;
use Barryvdh\DomPDF\Facade\Pdf;

class OperasionalPdfController extends Controller
{
    public function exportPdf($id)
    {
        // Fetch the operasional data with relationships
        $operasional = Operasional::with([
            'cabang',
            'pelabuhan',
            'layanan',
            // Add other relationships as needed
        ])->findOrFail($id);

        // Set default values for the PDF template
        $tanggal = now()->format('l, F d, Y'); // Format: Wednesday, August 06, 2025
        $waktu = now()->format('H:i'); // Format: 10:00

        // Generate PDF using the blade template
        $pdf = Pdf::loadView('pdf.operasional', compact('operasional', 'tanggal', 'waktu'))
            ->setPaper('a4', 'landscape');

        // Return the PDF as a stream download
        return $pdf->stream("laporan-operasional-{$id}.pdf");
    }

    public function exportPdfByPelabuhan($id)
    {
        // If you want to export based on pelabuhan data
        $pelabuhan = Pelabuhan::with([
            'layanan.perangkat',
            'operasional', // Add operasional relationship if exists
        ])->findOrFail($id);

        // You'll need to create a separate blade template for pelabuhan-based export
        $pdf = Pdf::loadView('pdf.pelabuhan-operasional', compact('pelabuhan'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("laporan-pelabuhan-{$id}.pdf");
    }

    public function exportPdfWithCustomData($id, $tanggal = null, $waktu = null)
    {
        $operasional = Operasional::with([
            'cabang',
            'pelabuhan',
            'layanan',
        ])->findOrFail($id);

        // Use provided date/time or default to current
        $tanggal = $tanggal ?? now()->format('l, F d, Y');
        $waktu = $waktu ?? now()->format('H:i');

        $pdf = Pdf::loadView('pdf.operasional', compact('operasional', 'tanggal', 'waktu'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("laporan-operasional-{$id}.pdf");
    }
}

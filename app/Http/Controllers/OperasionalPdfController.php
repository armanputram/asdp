<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Layanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class OperasionalPdfController extends Controller
{
    public function exportPdf($id)
    {
        try {
            // Ambil data operasional
            $operasional = Operasional::with([
                'cabang',
                'pelabuhan',
                'layanan',
                'user',
                'items'
            ])->findOrFail($id);

            // Ambil semua layanan dari pelabuhan
            $allLayanan = Layanan::with('perangkat')
                ->where('pelabuhan_id', $operasional->pelabuhan_id)
                ->get();

            if ($allLayanan->isEmpty()) {
                return response("Tidak ada layanan terdaftar untuk pelabuhan ini", 404);
            }

            // Ambil grouped records dengan items
            $groupedRecords = Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
                ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
                ->where('cabang_id', $operasional->cabang_id)
                ->where('user_id', $operasional->user_id)
                ->with(['layanan', 'items.perangkat'])
                ->get();

            // Debug log
            Log::info('PDF Generation - Grouped Records:', [
                'total' => $groupedRecords->count(),
                'data' => $groupedRecords->map(function($rec) {
                    return [
                        'id' => $rec->id,
                        'layanan' => $rec->layanan->nama ?? null,
                        'items_count' => $rec->items->count(),
                        'items_detail' => $rec->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'perangkat_id' => $item->perangkat_id,
                                'perangkat_nama' => $item->perangkat->nama ?? 'N/A',
                                'qty_check' => $item->qty_check,
                                'keterangan' => $item->keterangan ?? 'NULL',
                                'catatan' => $item->catatan ?? 'NULL',
                                'status_perangkat' => $item->status_perangkat ?? 'NULL',
                                'all_attributes' => $item->getAttributes(), // Tampilkan semua kolom
                            ];
                        })
                    ];
                })
            ]);

            // Build checklist data
            $checklistData = $this->buildChecklistData($allLayanan, $groupedRecords);

            if (empty($checklistData)) {
                return response("Tidak ada data perangkat untuk ditampilkan", 404);
            }

            // Format tanggal
            $carbonDate = \Carbon\Carbon::parse($operasional->created_at);
            $tanggal = $carbonDate->locale('id')->isoFormat('dddd, MMMM DD, YYYY');
            $waktu = $carbonDate->format('H:i');

            // Generate PDF
            $pdf = Pdf::loadView('pdf.operasional', [
                'operasional' => $operasional,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'pelabuhan' => $operasional->pelabuhan,
                'checklistData' => $checklistData,
                'tanggal_export' => now(),
                'total_layanan' => count($checklistData),
                'user' => $operasional->user,
                'cabang' => $operasional->cabang,
            ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

            $filename = 'checklist-' .
                       \Illuminate\Support\Str::slug($operasional->pelabuhan->nama) . '-' .
                       $carbonDate->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Data operasional ID {$id} tidak ditemukan");
            return response("Data operasional tidak ditemukan", 404);

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat generate PDF',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ], 500);
        }
    }

private function buildChecklistData($allLayanan, $groupedRecords)
{
    $checklistData = [];

    foreach ($allLayanan as $layanan) {
        $layananItems = [];

        if ($layanan->perangkat->isEmpty()) {
            continue;
        }

        foreach ($layanan->perangkat as $index => $perangkat) {
            $allItemsForPerangkat = collect();

            foreach ($groupedRecords as $opRecord) {
                if ($opRecord->layanan_id == $layanan->id) {
                    if ($opRecord->items && $opRecord->items->isNotEmpty()) {
                        $foundItems = $opRecord->items->where('perangkat_id', $perangkat->id);
                        if ($foundItems->isNotEmpty()) {
                            $allItemsForPerangkat = $allItemsForPerangkat->merge($foundItems);
                        }
                    }
                }
            }

            if ($allItemsForPerangkat->isEmpty()) {
                continue;
            }

            // PERBAIKAN: Status dan checks per lokasi
            $qtyChecks = [0, 0, 0, 0, 0, 0, 0, 0, 0];
            $statusPerLokasi = []; // Array untuk menyimpan status tiap lokasi
            $qty = 0;
            $keterangan = [];
            $catatanPerLokasi = [];
            $hasPhoto = false;

            foreach ($allItemsForPerangkat as $item) {
                $lokasiNumber = null;

                if (isset($item->qty_check) && $item->qty_check) {
                    $qtyCheckValue = is_string($item->qty_check) ? (int)$item->qty_check : $item->qty_check;

                    if ($qtyCheckValue > 0 && $qtyCheckValue <= 9) {
                        $checkIndex = $qtyCheckValue - 1;
                        $qtyChecks[$checkIndex] = 1;
                        $lokasiNumber = $qtyCheckValue;

                        // Simpan status untuk lokasi ini
                        $itemStatus = isset($item->status_perangkat) ? strtolower(trim($item->status_perangkat)) : 'baik';
                        $statusPerLokasi[$checkIndex] = $itemStatus;
                    }
                }

                if (isset($item->qty) && $item->qty && $item->qty > $qty) {
                    $qty = $item->qty;
                }

                // Keterangan dari perangkat
                if (isset($item->perangkat->keterangan) && $item->perangkat->keterangan) {
                    $ketValue = trim($item->perangkat->keterangan);
                    if ($ketValue !== '' && strtoupper($ketValue) !== 'NULL') {
                        $keterangan[] = $ketValue;
                    }
                }

                // Catatan dengan info lokasi
                if (isset($item->catatan) && $item->catatan) {
                    $catValue = trim($item->catatan);
                    if ($catValue !== '' && strtoupper($catValue) !== 'NULL') {
                        if ($lokasiNumber) {
                            $catatanPerLokasi[] = "Lokasi {$lokasiNumber}: {$catValue}";
                        } else {
                            $catatanPerLokasi[] = $catValue;
                        }
                    }
                }

                if (isset($item->foto) && $item->foto) {
                    $hasPhoto = true;
                }
            }

            if ($qty == 0) {
                $qty = $perangkat->qty ?? '-';
            }

            $keteranganStr = !empty($keterangan) ? implode(', ', array_unique($keterangan)) : '-';
            $catatanStr = !empty($catatanPerLokasi) ? implode(', ', $catatanPerLokasi) : '-';

            $layananItems[] = [
                'no' => $index + 1,
                'name' => $perangkat->nama ?? 'Perangkat',
                'qty' => $qty,
                'checks' => $qtyChecks,
                'status_per_lokasi' => $statusPerLokasi, // BARU: status per lokasi
                'desc' => $keteranganStr,
                'catatan' => $catatanStr,
                'doc' => $hasPhoto ? 'Ada' : '',
            ];
        }

        if (!empty($layananItems)) {
            $checklistData[$layanan->nama] = $layananItems;
        }
    }

    return $checklistData;
}
    public function downloadPdf($id)
    {
        try {
            $operasional = Operasional::with([
                'cabang',
                'pelabuhan',
                'layanan',
                'user',
                'items'
            ])->findOrFail($id);

            $allLayanan = Layanan::with('perangkat')
                ->where('pelabuhan_id', $operasional->pelabuhan_id)
                ->get();

            $groupedRecords = Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
                ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
                ->where('cabang_id', $operasional->cabang_id)
                ->where('user_id', $operasional->user_id)
                ->with(['layanan', 'items'])
                ->get();

            $checklistData = $this->buildChecklistData($allLayanan, $groupedRecords);

            $carbonDate = \Carbon\Carbon::parse($operasional->created_at);
            $tanggal = $carbonDate->locale('id')->isoFormat('dddd, MMMM DD, YYYY');
            $waktu = $carbonDate->format('H:i');

            $pdf = Pdf::loadView('pdf.operasional', [
                'operasional' => $operasional,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'pelabuhan' => $operasional->pelabuhan,
                'checklistData' => $checklistData,
                'tanggal_export' => now(),
                'total_layanan' => count($checklistData),
                'user' => $operasional->user,
                'cabang' => $operasional->cabang,
            ])->setPaper('a4', 'landscape');

            $filename = 'checklist-' .
                       \Illuminate\Support\Str::slug($operasional->pelabuhan->nama) . '-' .
                       $carbonDate->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error downloading PDF: ' . $e->getMessage());
            return response("Error: " . $e->getMessage(), 500);
        }
    }
}

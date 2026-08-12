<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Layanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class OperasionalPdfController extends Controller
{
    public function exportPdf($id)
    {
        try {
            $operasional = Operasional::with([
                'user',
                'cabang',
                'pelabuhan',
                'layanan',
                'items'
            ])->findOrFail($id);

            $allLayanan = Layanan::with('perangkat')
                ->where('pelabuhan_id', $operasional->pelabuhan_id)
                ->get();

            if ($allLayanan->isEmpty()) {
                return response("Tidak ada layanan terdaftar untuk pelabuhan ini", 404);
            }

            // Ambil semua operasional di pelabuhan + cabang + hari yang sama
            // TANPA filter user_id — semua petugas yang mengisi digabung
            $groupedRecords = Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
                ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
                ->where('cabang_id', $operasional->cabang_id)
                ->with(['layanan', 'items.perangkat'])
                ->get();

            Log::info('PDF Generation - Grouped Records:', [
                'total'      => $groupedRecords->count(),
                'pelabuhan'  => $operasional->pelabuhan_id,
                'tanggal'    => $operasional->created_at->format('Y-m-d'),
                'user_count' => $groupedRecords->pluck('user_id')->unique()->count(),
                'data'       => $groupedRecords->map(function ($rec) {
                    return [
                        'id'           => $rec->id,
                        'user_id'      => $rec->user_id,
                        'layanan'      => $rec->layanan->nama ?? null,
                        'items_count'  => $rec->items->count(),
                        'items_detail' => $rec->items->map(function ($item) {
                            return [
                                'id'               => $item->id,
                                'perangkat_id'     => $item->perangkat_id,
                                'perangkat_nama'   => $item->perangkat->nama ?? 'N/A',
                                'qty_check'        => $item->qty_check,
                                'keterangan'       => $item->keterangan ?? 'NULL',
                                'catatan'          => $item->catatan ?? 'NULL',
                                'status_perangkat' => $item->status_perangkat ?? 'NULL',
                                'foto'             => $item->foto ?? 'NULL',
                                'all_attributes'   => $item->getAttributes(),
                            ];
                        })
                    ];
                })
            ]);

            $checklistData = $this->buildChecklistData($allLayanan, $groupedRecords);

            if (empty($checklistData)) {
                return response("Tidak ada data perangkat untuk ditampilkan", 404);
            }

            $carbonDate = \Carbon\Carbon::parse($operasional->created_at);
            $tanggal    = $carbonDate->locale('id')->isoFormat('dddd, MMMM DD, YYYY');
            $waktu      = $carbonDate->format('H:i');

            $validatedByUser = $operasional->is_validated
                ? \App\Models\User::find($operasional->validated_by)
                : null;

            $pdf = Pdf::loadView('pdf.operasional', [
                'operasional'    => $operasional,
                'tanggal'        => $tanggal,
                'waktu'          => $waktu,
                'pelabuhan'      => $operasional->pelabuhan,
                'checklistData'  => $checklistData,
                'tanggal_export' => now(),
                'total_layanan'  => count($checklistData),
                'user'           => $operasional->user,
                'cabang'         => $operasional->cabang,
                'is_validated'   => $operasional->is_validated,
                'validated_by'   => $validatedByUser,
                'validated_at'   => $operasional->validated_at,
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
                'id'    => $id,
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error'   => 'Terjadi kesalahan saat generate PDF',
                'message' => $e->getMessage(),
                'file'    => basename($e->getFile()),
                'line'    => $e->getLine(),
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

                $qtyChecks        = [0, 0, 0, 0, 0, 0, 0, 0, 0];
                $statusPerLokasi  = [];
                $docPerLokasi     = [];
                $qty              = 0;
                $keterangan       = [];
                $catatanPerLokasi = [];
                $hasPhoto         = false;

                foreach ($allItemsForPerangkat as $item) {
                    $lokasiNumber = null;

                    if (isset($item->qty_check) && $item->qty_check) {
                        $qtyCheckValue = is_string($item->qty_check) ? (int)$item->qty_check : $item->qty_check;

                        if ($qtyCheckValue > 0 && $qtyCheckValue <= 9) {
                            $checkIndex             = $qtyCheckValue - 1;
                            $qtyChecks[$checkIndex] = 1;
                            $lokasiNumber           = $qtyCheckValue;

                            $itemStatus                   = isset($item->status_perangkat) ? strtolower(trim($item->status_perangkat)) : 'baik';
                            $statusPerLokasi[$checkIndex] = $itemStatus;

                            if (isset($item->foto) && !empty($item->foto)) {
                                $docPerLokasi[$lokasiNumber] = true;
                                $hasPhoto = true;
                            }
                        }
                    }

                    if (isset($item->qty) && $item->qty && $item->qty > $qty) {
                        $qty = $item->qty;
                    }

                    if (isset($item->perangkat->keterangan) && $item->perangkat->keterangan) {
                        $ketValue = trim($item->perangkat->keterangan);
                        if ($ketValue !== '' && strtoupper($ketValue) !== 'NULL') {
                            $keterangan[] = $ketValue;
                        }
                    }

                    if (isset($item->catatan) && $item->catatan) {
                        $catValue = trim($item->catatan);
                        if ($catValue !== '' && strtoupper($catValue) !== 'NULL') {
                            if ($lokasiNumber) {
                                $catatanPerLokasi[] = "Lok {$lokasiNumber}: {$catValue}";
                            } else {
                                $catatanPerLokasi[] = $catValue;
                            }
                        }
                    }
                }

                if ($qty == 0) {
                    $qty = $perangkat->qty ?? '-';
                }

                $layananItems[] = [
                    'no'                => $index + 1,
                    'name'              => $perangkat->nama ?? 'Perangkat',
                    'qty'               => $qty,
                    'checks'            => $qtyChecks,
                    'status_per_lokasi' => $statusPerLokasi,
                    'doc_per_lokasi'    => $docPerLokasi,
                    'desc'              => !empty($keterangan) ? implode(', ', array_unique($keterangan)) : '-',
                    'catatan'           => !empty($catatanPerLokasi) ? implode(', ', $catatanPerLokasi) : '-',
                    'doc'               => $hasPhoto ? 'Ada' : '',
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
                'user',
                'cabang',
                'pelabuhan',
                'layanan',
                'items'
            ])->findOrFail($id);

            $allLayanan = Layanan::with('perangkat')
                ->where('pelabuhan_id', $operasional->pelabuhan_id)
                ->get();

            // Semua operasional di pelabuhan + cabang + hari (semua user)
            $groupedRecords = Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
                ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
                ->where('cabang_id', $operasional->cabang_id)
                ->with(['layanan', 'items'])
                ->get();

            $checklistData = $this->buildChecklistData($allLayanan, $groupedRecords);

            $carbonDate = \Carbon\Carbon::parse($operasional->created_at);
            $tanggal    = $carbonDate->locale('id')->isoFormat('dddd, MMMM DD, YYYY');
            $waktu      = $carbonDate->format('H:i');

            $validatedByUser = $operasional->is_validated
                ? \App\Models\User::find($operasional->validated_by)
                : null;

            $pdf = Pdf::loadView('pdf.operasional', [
                'operasional'    => $operasional,
                'tanggal'        => $tanggal,
                'waktu'          => $waktu,
                'pelabuhan'      => $operasional->pelabuhan,
                'checklistData'  => $checklistData,
                'tanggal_export' => now(),
                'total_layanan'  => count($checklistData),
                'user'           => $operasional->user,
                'cabang'         => $operasional->cabang,
                'is_validated'   => $operasional->is_validated,
                'validated_by'   => $validatedByUser,
                'validated_at'   => $operasional->validated_at,
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
public function toggleValidasi(Request $request, Operasional $operasional)
{
    $aksi    = $request->input('aksi');
    $confirm = $request->boolean('confirm', false); // flag konfirmasi dari user

    if (! in_array($aksi, ['validasi', 'batalkan'])) {
        return response()->json(['success' => false, 'message' => 'Aksi tidak valid.'], 422);
    }

    // ── Cek belum dikerjakan (hanya saat validasi, dan belum konfirmasi) ──
    if ($aksi === 'validasi' && ! $confirm) {

        $groupedRecords = Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
            ->where('cabang_id', $operasional->cabang_id)
            ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
            ->with(['items', 'layanan'])
            ->get();

        $dikerjakanPerLayanan = [];
        foreach ($groupedRecords as $opRecord) {
            if (! $opRecord->layanan_id) continue;
            foreach ($opRecord->items as $item) {
                $dikerjakanPerLayanan[$opRecord->layanan_id][$item->perangkat_id] = true;
            }
        }

        $belumDikerjakan = [];
        foreach ($groupedRecords->unique('layanan_id') as $opRecord) {
            if (! $opRecord->layanan_id) continue;
            $layananNama = optional($opRecord->layanan)->nama ?? 'Tidak Diketahui';
            $sudahIds    = array_keys($dikerjakanPerLayanan[$opRecord->layanan_id] ?? []);
            $query       = \App\Models\Perangkat::where('layanan_id', $opRecord->layanan_id);
            if (! empty($sudahIds)) {
                $query->whereNotIn('id', $sudahIds);
            }
            $belum = $query->pluck('nama')->toArray();
            if (! empty($belum)) {
                $belumDikerjakan[$layananNama] = $belum;
            }
        }

        // Ada yang belum → kembalikan warning, minta konfirmasi
        if (! empty($belumDikerjakan)) {
            return response()->json([
                'success'          => false,
                'need_confirm'     => true,
                'belum_dikerjakan' => $belumDikerjakan,
            ]);
        }
    }

    // ── Proses validasi / batalkan ──────────────────────────────────────
    Operasional::where('pelabuhan_id', $operasional->pelabuhan_id)
        ->where('cabang_id', $operasional->cabang_id)
        ->whereDate('created_at', $operasional->created_at->format('Y-m-d'))
        ->update($aksi === 'validasi' ? [
            'is_validated' => true,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ] : [
            'is_validated' => false,
            'validated_by' => null,
            'validated_at' => null,
        ]);

    return response()->json(['success' => true]);
}
}

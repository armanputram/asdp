<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use App\Services\AsistenAiService; // <-- Import Service
use App\Models\OperasionalItem;   // <-- Import Model
use App\Models\Perangkat;          // <-- Import Model
use App\Models\CatatanPerangkat;   // <-- Import Model CatatanPerangkat
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AsistenVirtual extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static string $view = 'filament.pages.asisten-virtual';

    // Properti untuk menyimpan input dan output
    public ?string $query = '';
    public ?string $result = null;

    // Definisikan form input
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('query')
                    ->label('Tanyakan sesuatu...')
                    ->placeholder("Contoh: 'Total penumpang di Merak kemarin' atau 'Rekap perangkat rusak bulan ini'")
                    ->required(),
            ])
            ->statePath('data'); // Simpan state form di $this->data
    }

    // Fungsi ini dipanggil saat tombol "Tanya" di-klik
    public function askAi(AsistenAiService $aiService)
    {
        $this->result = null; // Reset hasil
        $this->query = $this->form->getState()['query']; // Ambil query dari form

        // 1. Panggil AI untuk dapat JSON
        $json = $aiService->queryToJson($this->query);

        if (!$json) {
            $this->result = '<p>Maaf, saya tidak mengerti atau terjadi error saat menghubungi AI.</p>';
            return;
        }

        // 2. Bangun Query Eloquent dan dapatkan hasilnya
        $this->result = $this->buildQueryAndGetResponse($json);
    }

    /**
     * Fungsi utama untuk menerjemahkan JSON dari AI menjadi Query Eloquent
     */
    private function buildQueryAndGetResponse(array $json): string
    {
        $intent = $json['intent'] ?? null;

        // Gunakan switch-case berdasarkan 'intent'
        switch ($intent) {
            case 'query_operasional':
                return $this->handleQueryOperasional($json);
            case 'query_perangkat':
                return $this->handleQueryPerangkat($json);

            // --- INI INTENT BARU UNTUK REKAPITULASI ---
            case 'summarize_perangkat':
                return $this->handleSummarizePerangkat($json);
            // --- AKHIR TAMBAHAN ---

            default:
                return '<p>Maaf, saya tidak yakin apa yang Anda maksud. Coba tanyakan tentang data operasional atau status perangkat.</p>';
        }
    }

    /**
     * Menangani query untuk data operasional (penumpang, kendaraan, pendapatan)
     */
    private function handleQueryOperasional(array $json): string
    {
        $lokasi = $json['lokasi'] ?? null;
        $targetData = $json['target_data'] ?? null;
        $metric = $json['target_metric'] ?? 'jumlah'; // default 'jumlah'
        $tglMulai = $json['tanggal_mulai'] ?? Carbon::today()->toDateString();
        $tglAkhir = $json['tanggal_akhir'] ?? $tglMulai;

        $query = OperasionalItem::query();

        $query->whereHas('operasional', function (Builder $q) use ($lokasi, $tglMulai, $tglAkhir) {
            if ($lokasi) {
                $q->whereHas('pelabuhan', fn(Builder $p) => $p->where('nama', 'like', '%' . $lokasi . '%'));
            }
            $q->whereBetween('tanggal', [$tglMulai, $tglAkhir]);
        });

        if ($targetData) {
            $query->whereHas('layanan', fn(Builder $l) => $l->where('nama', 'like', '%' . $targetData . '%'));
        }

        $metricAman = in_array($metric, ['jumlah', 'total_pendapatan']) ? $metric : 'jumlah';
        $total = $query->sum($metricAman);

        $jawaban = "<p>Berikut hasilnya:</p><strong>Total " . Str::title(str_replace('_', ' ', $metricAman)) . "</strong>";
        if ($targetData) $jawaban .= " untuk <strong>" . Str::title($targetData) . "</strong>";
        if ($lokasi) $jawaban .= " di <strong>" . Str::title($lokasi) . "</strong>";
        if ($tglMulai) $jawaban .= " dari <strong>$tglMulai</strong> s/d <strong>$tglAkhir</strong>";
        $jawaban .= " adalah: <br><strong style='font-size: 1.5rem;'>" . number_format($total) . "</strong>";

        return $jawaban;
    }

    /**
     * Menangani query untuk status perangkat (CCTV, VTS, dll)
     */
    private function handleQueryPerangkat(array $json): string
    {
        $lokasi = $json['lokasi'] ?? null;
        $targetData = $json['target_data'] ?? null; // e.g., "CCTV"

        $query = Perangkat::query(); // Ini query ke tabel master perangkat

        if ($lokasi) {
            $query->whereHas('pelabuhan', fn(Builder $p) => $p->where('nama', 'like', '%' . $lokasi . '%'));
        }
        if ($targetData) {
            $query->where('nama', 'like', '%' . $targetData . '%');
        }

        $perangkats = $query->get(['nama', 'status', 'pelabuhan_id'])->load('pelabuhan');

        if ($perangkats->isEmpty()) {
            return "<p>Tidak ditemukan perangkat " . ($targetData ? Str::title($targetData) : '') . " " . ($lokasi ? "di " . Str::title($lokasi) : '') . ".</p>";
        }

        $jawaban = "<p>Berikut adalah kondisi perangkat (berdasarkan data master):</p>";
        $jawaban .= "<ul>";
        foreach ($perangkats as $perangkat) {
            $lokasiPerangkat = $perangkat->pelabuhan ? $perangkat->pelabuhan->nama : 'N/A';
            $jawaban .= "<li><strong>" . $perangkat->nama . "</strong> di <strong>$lokasiPerangkat</strong> &mdash; Status: <strong>" . $perangkat->status . "</strong></li>";
        }
        $jawaban .= "</ul>";

        return $jawaban;
    }

    /**
     * FUNGSI BARU: Menangani query rekapitulasi untuk perangkat rusak
     * Ini query ke tabel log/catatan harian.
     */
    private function handleSummarizePerangkat(array $json): string
    {
        // Ambil data dari JSON, gunakan default "bulan ini" jika tidak ada
        $tglMulai = $json['tanggal_mulai'] ?? Carbon::now()->startOfMonth()->toDateString();
        $tglAkhir = $json['tanggal_akhir'] ?? Carbon::now()->toDateString();
        $statusFilter = $json['status_filter'] ?? 'Rusak'; // Default mencari yang 'Rusak'
        $lokasi = $json['lokasi'] ?? null;

        // Query ke tabel 'catatan_perangkats'
        $query = CatatanPerangkat::query();

        // Filter status dan rentang tanggal
        $query->where('status', $statusFilter)
              ->whereBetween('created_at', [$tglMulai, $tglAkhir]);

        // Filter lokasi (JIKA DIMINTA SAMA AI)
        if ($lokasi) {
             $query->whereHas('perangkat.pelabuhan', function (Builder $q) use ($lokasi) {
                $q->where('nama', 'like', '%' . $lokasi . '%');
            });
        }

        // Ambil data, kelompokkan berdasarkan NAMA PELABUHAN
        $results = $query
            ->with('perangkat.pelabuhan')
            ->get()
            ->groupBy('perangkat.pelabuhan.nama')
            ->map(fn($catatanGroup) => $catatanGroup->count()) // Hitung jumlah
            ->sortDesc(); // Urutkan dari yg paling banyak


        // --- Buat Tampilan Jawabannya ---
        if ($results->isEmpty()) {
            return "<p>Tidak ada perangkat yang dilaporkan dengan status '<strong>$statusFilter</strong>' "
                   . ($lokasi ? "di $lokasi " : "")
                   . "dalam periode $tglMulai s/d $tglAkhir.</p>";
        }

        $jawaban = "<p>Berikut adalah rekapitulasi perangkat yang dilaporkan '<strong>$statusFilter</strong>'
                    dari tanggal <strong>$tglMulai</strong> s/d <strong>$tglAkhir</strong>,
                    diurutkan dari yang terbanyak:</p>";

        $jawaban .= "<ul>";
        foreach ($results as $pelabuhanNama => $count) {
            $namaPelabuhan = $pelabuhanNama ?: 'Lokasi Tidak Terdefinisi';
            $jawaban .= "<li><strong>" . htmlspecialchars($namaPelabuhan) . ":</strong> " . $count . " laporan</li>";
        }
        $jawaban .= "</ul>";

        return $jawaban;
    }
}

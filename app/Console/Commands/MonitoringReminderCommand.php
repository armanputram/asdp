<?php

namespace App\Console\Commands;

use App\Models\Operasional;
use App\Models\Pelabuhan;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class MonitoringReminderCommand extends Command
{
    protected $signature   = 'monitoring:reminder';
    protected $description = 'Kirim notifikasi jika ada layanan yang belum dilaporkan (semua tanggal)';

    public function handle(): void
    {
        $this->info("Mengecek semua laporan yang belum lengkap...");

        // Ambil semua pelabuhan yang punya layanan
        $semuaPelabuhan = Pelabuhan::whereHas('layanans')->with('layanans')->get();

        if ($semuaPelabuhan->isEmpty()) {
            $this->info('Tidak ada pelabuhan dengan layanan. Selesai.');
            return;
        }

        // Ambil semua tanggal operasional yang ada (sampai kemarin)
        $semuaTanggal = Operasional::whereDate('created_at', '<=', Carbon::yesterday()->toDateString())
            ->selectRaw('DATE(created_at) as tanggal, cabang_id')
            ->groupBy('tanggal', 'cabang_id')
            ->orderBy('tanggal', 'desc')
            ->get();

        if ($semuaTanggal->isEmpty()) {
            $this->info('Tidak ada laporan. Selesai.');
            return;
        }

        $users      = User::all();
        $totalKirim = 0;

        foreach ($semuaTanggal as $record) {
            $tanggal  = $record->tanggal;
            $cabangId = $record->cabang_id;

            foreach ($semuaPelabuhan as $pelabuhan) {
                $semuaLayanan = $pelabuhan->layanans;
                if ($semuaLayanan->isEmpty()) continue;

                // Layanan yang sudah dilaporkan pada tanggal ini
                $layananTerlaporIds = Operasional::where('pelabuhan_id', $pelabuhan->id)
                    ->where('cabang_id', $cabangId)
                    ->whereDate('created_at', $tanggal)
                    ->pluck('layanan_id')
                    ->unique()
                    ->toArray();

                // Layanan yang belum dilaporkan
                $belumLapor = $semuaLayanan->filter(fn($l) => !in_array($l->id, $layananTerlaporIds));

                if ($belumLapor->isEmpty()) {
                    $this->info("  ✓ [{$pelabuhan->nama} - {$tanggal}] Semua layanan sudah dilaporkan");
                    continue;
                }

                $namaLayananBelum = $belumLapor->pluck('nama')->toArray();
                $total            = count($namaLayananBelum);
                $preview          = implode(', ', array_slice($namaLayananBelum, 0, 2));
                $lebih            = $total > 2 ? ' (+' . ($total - 2) . ' lainnya)' : '';
                $tanggalFormatted = Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY');

                foreach ($users as $user) {
                    Notification::make()
                        ->title(' Layanan Belum Dilaporkan — ' . $pelabuhan->nama)
                        ->body("Tanggal {$tanggalFormatted}: {$total}/{$semuaLayanan->count()} layanan belum dimonitor. Belum: {$preview}{$lebih}")
                        ->icon('heroicon-o-exclamation-triangle')
                        ->iconColor('warning')
                        ->warning()
                        ->sendToDatabase($user);
                    $totalKirim++;
                }

                $this->warn("  ⚠ [{$pelabuhan->nama} - {$tanggal}] {$total}/{$semuaLayanan->count()} layanan belum dilaporkan → notif ke {$users->count()} user");
            }
        }

        $this->info("Selesai. Total notifikasi terkirim: {$totalKirim}");
    }
}

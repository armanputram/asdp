<?php

namespace App\Listeners;

use App\Models\Operasional;
use App\Models\Pelabuhan;
// use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class SendMonitoringReminder
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Hapus notif monitoring lama milik user ini
        DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('data', 'like', '%Layanan Belum Dilaporkan%')
            ->delete();

        $semuaPelabuhan = Pelabuhan::whereHas('layanans')->with('layanans')->get();

        // Ambil semua tanggal yang pernah ada operasional (sampai kemarin)
        $semuaTanggal = Operasional::whereDate('created_at', '<=', Carbon::yesterday())
            ->selectRaw('DATE(created_at) as tanggal')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal');

        foreach ($semuaTanggal as $tanggal) {
            foreach ($semuaPelabuhan as $pelabuhan) {
                $semuaLayanan = $pelabuhan->layanans;
                if ($semuaLayanan->isEmpty()) continue;

                $layananTerlaporIds = Operasional::where('pelabuhan_id', $pelabuhan->id)
                    ->whereDate('created_at', $tanggal)
                    ->pluck('layanan_id')
                    ->unique()
                    ->toArray();

                $belumLapor = $semuaLayanan->filter(fn($l) => !in_array($l->id, $layananTerlaporIds));
                if ($belumLapor->isEmpty()) continue;

                $namaLayananBelum = $belumLapor->pluck('nama')->toArray();
                $total            = count($namaLayananBelum);
                $preview          = implode(', ', array_slice($namaLayananBelum, 0, 2));
                $lebih            = $total > 2 ? ' (+' . ($total - 2) . ' lainnya)' : '';
                $tanggalFormatted = Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY');

                Notification::make()
                    ->title('Layanan Belum Dilaporkan — ' . $pelabuhan->nama)
                    ->body("Tanggal {$tanggalFormatted}: {$total}/{$semuaLayanan->count()} layanan belum dimonitor. Belum: {$preview}{$lebih}")
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->warning()
                    ->sendToDatabase($user);
            }
        }
    }
}

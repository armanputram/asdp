<?php

namespace App\Observers;

use App\Models\Operasional;
use App\Models\Pelabuhan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OperasionalObserver
{
    public function created(Operasional $operasional): void
    {
        $tanggal   = $operasional->created_at->toDateString();
        $pelabuhan = Pelabuhan::with('layanans')->find($operasional->pelabuhan_id);

        if (!$pelabuhan || $pelabuhan->layanans->isEmpty()) return;

        $semuaLayananIds = $pelabuhan->layanans->pluck('id')->toArray();

        $layananTerlaporIds = Operasional::where('pelabuhan_id', $pelabuhan->id)
            ->whereDate('created_at', $tanggal)
            ->pluck('layanan_id')
            ->unique()
            ->toArray();

        $belumLapor = array_diff($semuaLayananIds, $layananTerlaporIds);

        if (empty($belumLapor)) {
            foreach (User::all() as $user) {
                DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->where('data', 'like', '%Layanan Belum Dilaporkan — ' . $pelabuhan->nama . '%')
                    ->delete();
            }
        }
    }
}

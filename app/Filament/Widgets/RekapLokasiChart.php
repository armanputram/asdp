<?php

namespace App\Filament\Widgets;

// ❌ Hapus baris ini
// use App\Models\Perangkat;

use App\Models\Pelabuhan;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RekapLokasiChart extends ApexChartWidget
{
    protected static ?string $heading = 'Rekap Perangkat Berdasarkan Lokasi';
    protected static ?string $chartId = 'rekapLokasiChart'; // ← tambah ?

    protected function getOptions(): array
    {
        $pelabuhanList = Pelabuhan::withCount('perangkats')->get();

        return [
            'chart'  => ['type' => 'bar', 'height' => 250],
            'series' => [['name' => 'Perangkat', 'data' => $pelabuhanList->pluck('perangkats_count')->toArray()]],
            'xaxis'  => ['categories' => $pelabuhanList->pluck('nama')->toArray()],
            'colors' => ['#8b5cf6'],
        ];
    }
}

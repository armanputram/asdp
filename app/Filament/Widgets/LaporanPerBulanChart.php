<?php

namespace App\Filament\Widgets;

use App\Models\Operasional;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LaporanPerBulanChart extends ApexChartWidget
{
    protected static ?string $heading = 'Jumlah Laporan Operasional per Bulan';
    protected static ?string $chartId = 'laporanPerBulanChart';

    protected function getOptions(): array
    {
        $data = Operasional::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $bulan  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $series = collect(range(1, 12))->map(fn($b) => $data[$b] ?? 0)->toArray();

        return [
            'chart'  => ['type' => 'bar', 'height' => 250],
            'series' => [['name' => 'Laporan', 'data' => $series]],
            'xaxis'  => ['categories' => $bulan],
            'colors' => ['#6366f1'],
        ];
    }
}

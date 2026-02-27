<?php

namespace App\Filament\Widgets;

use App\Models\CatatanPerangkat;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GangguanPerBulanChart extends ApexChartWidget
{
    protected static ?string $chartId = 'gangguanPerBulanChart';
    protected static ?string $heading = 'Jumlah Gangguan Perangkat IT per Bulan';

    protected function getOptions(): array
    {
        $data = CatatanPerangkat::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->whereNull('deleted_at')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $series = collect(range(1, 12))->map(fn($b) => $data[$b] ?? 0)->toArray();

   return [
    'chart' => [
        'type' => 'polarArea',
        'height' => 300,
    ],

    'series' => $series,
    'labels' => $bulan,

    // 👉 INI KUNCI UTAMANYA
    'colors' => [
        '#6366f1', // indigo
        '#22c55e', // green
        '#f59e0b', // amber
        '#ef4444', // red
        '#06b6d4', // cyan
        '#8b5cf6', // violet
        '#ec4899', // pink
        '#10b981', // emerald
        '#eab308', // yellow
        '#3b82f6', // blue
        '#f97316', // orange
        '#14b8a6', // teal
    ],

    'legend' => [
        'position' => 'right',
        'labels' => [
            'colors' => '#9ca3af',
            'fontWeight' => 600,
        ],
    ],

    'dataLabels' => [
        'enabled' => true,
    ],

    'stroke' => [
        'width' => 2,
        'colors' => ['#fff'],
    ],

    'fill' => [
        'opacity' => 0.9,
    ],
];
    }
}

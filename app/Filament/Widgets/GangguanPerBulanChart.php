<?php

namespace App\Filament\Widgets;

use App\Models\OperasionalItem;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GangguanPerBulanChart extends ApexChartWidget
{
    protected static ?string $chartId = 'gangguanPerBulanChart';
    protected static ?string $heading = 'Jumlah Gangguan Perangkat IT per Bulan';

    protected function getOptions(): array
    {
        // Label bulan untuk sumbu X chart
        $bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        $data = OperasionalItem::selectRaw('MONTH(tanggal) as bulan, COUNT(*) as total')
            ->where('status_perangkat', 'rusak') // hanya perangkat rusak
            ->whereYear('tanggal', now()->year)
            ->whereNull('deleted_at')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        // Isi 0 untuk bulan yang tidak ada datanya supaya tetap 12 titik (Jan-Des)
        $series = collect(range(1, 12))->map(fn($b) => $data[$b] ?? 0)->toArray();

        return [
            'chart' => [
                'type' => 'area', // disamakan dengan ProgressMonitoringChart
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Jumlah Gangguan',
                    'data' => $series,
                ],
            ],
            'xaxis' => [
                'categories' => $bulanLabel,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            // satu warna karena cuma satu series
            'colors' => ['#6366f1'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'fontFamily' => 'inherit',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.3,
                ],
            ],
        ];
    }
}

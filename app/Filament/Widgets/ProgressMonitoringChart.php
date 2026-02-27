<?php

namespace App\Filament\Widgets;

use App\Models\CatatanPerangkat;
use App\Models\Pelabuhan;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProgressMonitoringChart extends ApexChartWidget
{
    protected static ?string $chartId = 'progressMonitoringChart';
    protected static ?string $heading = 'Progress Monitoring IT Support';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        $pelabuhans = Pelabuhan::orderBy('nama')->pluck('nama', 'id')->toArray();
        return array_merge(['all' => 'Semua Pelabuhan'], $pelabuhans);
    }

    protected function getOptions(): array
    {
        $bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        // Query untuk Selesai
        $querySelesai = CatatanPerangkat::selectRaw('MONTH(catatan_perangkat.created_at) as bulan, COUNT(*) as total')
            ->whereYear('catatan_perangkat.created_at', now()->year)
            ->where('catatan_perangkat.is_selesai', 1)
            ->whereNull('catatan_perangkat.deleted_at');

        // Query untuk Belum Selesai
        $queryBelum = CatatanPerangkat::selectRaw('MONTH(catatan_perangkat.created_at) as bulan, COUNT(*) as total')
            ->whereYear('catatan_perangkat.created_at', now()->year)
            ->where('catatan_perangkat.is_selesai', 0)
            ->whereNull('catatan_perangkat.deleted_at');

        // Filter berdasarkan pelabuhan jika dipilih
        if ($this->filter !== 'all') {
            $pelabuhanId = (int) $this->filter;

            $querySelesai->join('perangkat', 'catatan_perangkat.perangkat_id', '=', 'perangkat.id')
                ->where('perangkat.pelabuhan_id', $pelabuhanId);

            $queryBelum->join('perangkat', 'catatan_perangkat.perangkat_id', '=', 'perangkat.id')
                ->where('perangkat.pelabuhan_id', $pelabuhanId);
        }

        $selesai = $querySelesai->groupBy('bulan')->orderBy('bulan')->pluck('total', 'bulan');
        $belum = $queryBelum->groupBy('bulan')->orderBy('bulan')->pluck('total', 'bulan');

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Selesai',
                    'data' => collect(range(1, 12))->map(fn($b) => $selesai[$b] ?? 0)->toArray(),
                ],
                [
                    'name' => 'Belum Selesai',
                    'data' => collect(range(1, 12))->map(fn($b) => $belum[$b] ?? 0)->toArray(),
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
            'colors' => ['#22c55e', '#ef4444'],
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

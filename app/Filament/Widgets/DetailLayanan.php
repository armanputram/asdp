<?php

namespace App\Filament\Widgets;

use App\Models\Pelabuhan;
use App\Models\Layanan;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DetailLayanan extends ApexChartWidget
{
    protected static ?string $chartId = 'detailLayananPelabuhan';
    protected static ?string $heading = 'Detail Layanan per Pelabuhan';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua Pelabuhan',
            '1' => 'Ketapang',
            '2' => 'Gilimanuk',
        ];
    }

    protected function getOptions(): array
    {
        if ($this->filter === 'all') {
            $pelabuhans = Pelabuhan::withCount('layanans')
                ->orderBy('layanans_count', 'asc')
                ->get();

            $categories = $pelabuhans->pluck('nama')->toArray();
            $data = $pelabuhans->pluck('layanans_count')->toArray();
        } else {
            $pelabuhanId = (int) $this->filter;

            // Ambil semua layanan di pelabuhan ini dengan sum qty perangkat
            $layanans = Layanan::where('pelabuhan_id', $pelabuhanId)
                ->withSum('perangkat', 'qty')
                ->orderBy('perangkat_sum_qty', 'asc')
                ->get();

            if ($layanans->isEmpty()) {
                $categories = ['Belum ada layanan'];
                $data = [0];
            } else {
                $categories = $layanans->pluck('nama')->toArray();
                $data = $layanans->map(fn($l) => $l->perangkat_sum_qty ?? 0)->toArray();
            }
        }
return [
    'chart' => [
        'type' => 'bar',
        'height' => 315,
    ],
    'series' => [
        [
            'name' => $this->filter === 'all' ? 'Jumlah Layanan' : 'Jumlah Perangkat',
            'data' => $data,
        ],
    ],
    'xaxis' => [
        'categories' => $categories,
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
    'colors' => ['#6366f1'],
];
    }
}

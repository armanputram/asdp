<?php

namespace App\Filament\Widgets;

use App\Models\Pelabuhan;
use App\Models\Layanan;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Support\RawJs;

class DetailLayanan extends ApexChartWidget
{
    protected static ?string $chartId = 'detailLayananPelabuhan';
    protected static ?string $heading = 'Detail Layanan per Pelabuhan';

    public ?string $filter = 'all';

  protected function getFilters(): ?array
{
    // Pastikan urutan pluck benar: pluck('nama', 'id')
    $pelabuhans = Pelabuhan::orderBy('nama')->pluck('nama', 'id')->toArray();

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
            'height' => 300,
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
        'plotOptions' => [
            'bar' => [
                'borderRadius' => 0,
                'horizontal' => true,
                'distributed' => true,
                'barHeight' => '80%',
                'isFunnel' => true,
            ]
        ],
       'colors' => [
    '#6366F1', // Indigo
    '#F59E0B', // Amber
    '#10B981', // Emerald
    '#EF4444', // Red
    '#8B5CF6', // Violet
    '#06B6D4', // Cyan
    '#F97316', // Orange
    '#EC4899', // Pink
],
    ];
}

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            dataLabels: {
                enabled: true,
                formatter: function (val, opt) {
                    return opt.w.globals.labels[opt.dataPointIndex]
                },
                dropShadow: {
                    enabled: true
                },
            }
        }
        JS);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Operasional;
use App\Models\Pelabuhan; // sesuaikan namespace model
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Forms\Components\Select;

class LaporanPerBulanChart extends ApexChartWidget
{
    protected static ?string $heading = 'Jumlah Laporan Operasional per Bulan';
    protected static ?string $chartId = 'laporanPerBulanChart';

    protected function getFormSchema(): array
    {
        return [
            Select::make('tahun')
                ->label('Tahun')
                ->options(
                    collect(range(now()->year, now()->year - 4))
                        ->mapWithKeys(fn($y) => [$y => $y])
                        ->toArray()
                )
                ->default(now()->year)
                ->required(),

            Select::make('pelabuhan_id')
                ->label('Pelabuhan')
                ->options(
                    Pelabuhan::pluck('nama', 'id')->toArray() // sesuaikan kolom nama & id
                )
                ->placeholder('Semua Pelabuhan')
                ->nullable(),
        ];
    }

    protected function getOptions(): array
    {
        $tahun       = $this->filterFormData['tahun'] ?? now()->year;
        $pelabuhanId = $this->filterFormData['pelabuhan_id'] ?? null;

        $query = Operasional::selectRaw('MONTH(created_at) as bulan, COUNT(DISTINCT DATE(created_at)) as total')
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->orderBy('bulan');

        if ($pelabuhanId) {
            $query->where('pelabuhan_id', $pelabuhanId); // sesuaikan nama kolom FK
        }

        $data = $query->pluck('total', 'bulan');

        $bulan  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $series = collect(range(1, 12))->map(fn($b) => $data[$b] ?? 0)->toArray();

       return [
    'chart'  => ['type' => 'bar', 'height' => 315],
    'series' => [['name' => 'Laporan', 'data' => $series]],
    'xaxis'  => ['categories' => $bulan],
    'yaxis'  => [
        'min'        => 0,
        'tickAmount' => max($series) ?: 1,
    ],
    'colors' => ['#6366f1'],
];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\OperasionalItem;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class StatusPerangkatChart extends ApexChartWidget
{
    protected static ?string $heading = 'Status Kondisi Perangkat IT';
    protected static ?string $chartId = 'statusPerangkatChart';
    protected static ?int $contentHeight = 200;

    protected function getOptions(): array
    {
        $baik  = OperasionalItem::where('status_perangkat', 'Baik')->count();
        $rusak = OperasionalItem::where('status_perangkat', 'Rusak')->count();

        return [
            'chart'  => ['type' => 'donut', 'height' => 200],
            'series' => [$baik, $rusak],
            'labels' => ['Baik', 'Rusak'],
            'colors' => ['#22c55e', '#ef4444'],
            'legend' => ['position' => 'bottom'],
        ];
    }
}

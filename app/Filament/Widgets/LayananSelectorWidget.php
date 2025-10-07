<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Operasional;
use App\Models\Layanan;

class LayananSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.layanan-selector-simple';

    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['filterUpdated' => '$refresh'];

       public static function canView(): bool
    {
        // Cek apakah sedang di halaman operasional
        return request()->routeIs('filament.admin.resources.operasionals.index');
    }

    public $selectedLayanan = null;
    public $showTable = false;

    public function mount()
    {
        $this->selectedLayanan = session('selected_layanan_filter');
        $this->showTable = session('show_operasional_table', false);
    }

    public function selectLayanan($layananName)
    {
        $this->selectedLayanan = $layananName;
        $this->showTable = true;

        session([
            'selected_layanan_filter' => $layananName,
            'show_operasional_table' => true
        ]);

        $layanan = Layanan::where('nama', $layananName)->first();
        $layananId = $layanan ? $layanan->id : null;

        $this->dispatch('layanan-filter-selected', layananId: $layananId, layananName: $layananName);
    }

    public function resetFilter()
    {
        $this->selectedLayanan = null;
        $this->showTable = false;
        session()->forget(['selected_layanan_filter', 'show_operasional_table']);

        $this->dispatch('layanan-filter-reset');
    }

    public function getViewData(): array
    {
        $layananOptions = [
            [
                'name' => 'LOKET PEJALAN KAKI',
                'icon' => '🚶‍♂️',
                'color' => 'bg-gradient-to-br from-blue-500 to-cyan-500',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'LOKET PEJALAN KAKI'))->count()
            ],
            [
                'name' => 'TOLGATE (R4, TRUCK, LCM)',
                'icon' => '🚛',
                'color' => 'bg-gradient-to-br from-green-500 to-emerald-500',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'TOLGATE (R4, TRUCK, LCM)'))->count()
            ],
            [
                'name' => 'TOLGATE (R2)',
                'icon' => '🏍️',
                'color' => 'bg-gradient-to-br from-purple-500 to-pink-500',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'TOLGATE (R2)'))->count()
            ],
            [
                'name' => 'LOKET BULUSAN',
                'icon' => '🎫',
                'color' => 'bg-gradient-to-br from-orange-500 to-red-500',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'LOKET BULUSAN'))->count()
            ],
            [
                'name' => 'RAK SERVER E-Ticketing',
                'icon' => '🖥️',
                'color' => 'bg-gradient-to-br from-indigo-500 to-blue-600',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'RAK SERVER E-Ticketing'))->count()
            ],
            [
                'name' => 'RAK SERVER TOLGATE',
                'icon' => '🗄️',
                'color' => 'bg-gradient-to-br from-teal-500 to-cyan-600',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'RAK SERVER TOLGATE'))->count()
            ],
            [
                'name' => 'DERMAGA',
                'icon' => '⚓',
                'color' => 'bg-gradient-to-br from-blue-600 to-teal-600',
                'count' => Operasional::whereHas('layanan', fn($q) => $q->where('nama', 'DERMAGA'))->count()
            ],
        ];

        array_unshift($layananOptions, [
            'name' => 'SEMUA LAYANAN',
            'icon' => '📊',
            'color' => 'bg-gradient-to-br from-gray-500 to-gray-600',
            'count' => Operasional::count(),
            'is_all' => true
        ]);

        return [
            'layananOptions' => $layananOptions,
            'selectedLayanan' => $this->selectedLayanan,
            'showTable' => $this->showTable
        ];
    }
}

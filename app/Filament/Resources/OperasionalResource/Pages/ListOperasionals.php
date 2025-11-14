<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Layanan;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;

class ListOperasionals extends ListRecords
{
    protected static string $resource = OperasionalResource::class;

    // Listen untuk event dari widget
    protected $listeners = [
        'layanan-filter-selected' => 'handleLayananFilterSelected',
        'layanan-filter-reset' => 'handleLayananFilterReset'
    ];

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Tambah Data Operasional'),
        ];
    }

    // Widget Card Selector di atas table
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\LayananSelectorWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Data Operasional - Pilih Layanan';
    }

    public function getSubheading(): string
    {
        return 'Klik pada kartu layanan untuk memfilter data operasional';
    }

    // Handle event ketika layanan dipilih dari widget
    public function handleLayananFilterSelected($layananName)
    {
        // Show table
        $this->hideTable = false;

        // Update table berdasarkan layanan yang dipilih
        $this->applyLayananFilter($layananName);

        // Emit event untuk update widget
        $this->dispatch('filterUpdated');
    }

    // Handle event ketika filter di-reset dari widget
    public function handleLayananFilterReset()
    {
        // Hide table
        $this->hideTable = true;

        // Reset all filters
        $this->tableFilters = [];

        // Reset table
        $this->resetTable();

        // Emit event untuk update widget
        $this->dispatch('filterUpdated');
    }

    // Apply filter berdasarkan layanan
    private function applyLayananFilter($layananName)
    {
        if ($layananName === 'SEMUA LAYANAN') {
            // Tidak perlu filter jika semua layanan
            $this->tableFilters = [];
        } else {
            // Find layanan by name
            $layanan = Layanan::where('nama', $layananName)->first();

            if ($layanan) {
                // Set filter menggunakan table filters
                $this->tableFilters = [
                    'layanan' => ['value' => $layanan->id]
                ];
            }
        }

        // Reset table untuk apply filter
        $this->resetTable();
    }

    // Override mount untuk restore filter dari session
    public function mount(): void
    {
        parent::mount();

        // Check if there's a saved filter
        $savedLayananFilter = session('selected_layanan_filter');
        if ($savedLayananFilter) {
            $this->hideTable = false;
            $this->applyLayananFilter($savedLayananFilter);
        }
    }

    // Override table method untuk menambahkan filters dan kondisi
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cabang.nama')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelabuhan.nama')
                    ->label('Pelabuhan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('layanan.nama')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty_check')
                    ->label('Titik Lokasi')
                    ->getStateUsing(function ($record) {
                        $firstItem = $record->items()->first();
                        return $firstItem && $firstItem->qty_check ? "Lokasi {$firstItem->qty_check}" : 'Tidak ada data';
                    })
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->getStateUsing(function ($record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->tanggal->format('d/m/Y') : 'Tidak ada data';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu')
                    ->getStateUsing(function ($record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->waktu->format('H:i') : 'Tidak ada data';
                    }),

                Tables\Columns\TextColumn::make('total_perangkat')
                    ->label('Total Perangkat')
                    ->getStateUsing(function ($record) {
                        return $record->items()->count();
                    })
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        $items = $record->items;
                        $baik = $items->where('status_perangkat', 'baik')->count();
                        $rusak = $items->where('status_perangkat', 'rusak')->count();
                        return "Baik: {$baik} | Rusak: {$rusak}";
                    })
                    ->badge()
                    ->color(function ($record) {
                        $rusak = $record->items->where('status_perangkat', 'rusak')->count();
                        return $rusak > 0 ? 'danger' : 'success';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('layanan')
                    ->relationship('layanan', 'nama')
                    ->label('Filter Layanan')
                    ->placeholder('Pilih Layanan'),

                Tables\Filters\SelectFilter::make('cabang')
                    ->relationship('cabang', 'nama')
                    ->label('Filter Cabang')
                    ->placeholder('Pilih Cabang'),

                Tables\Filters\SelectFilter::make('pelabuhan')
                    ->relationship('pelabuhan', 'nama')
                    ->label('Filter Pelabuhan')
                    ->placeholder('Pilih Pelabuhan'),

                Tables\Filters\SelectFilter::make('qty_check')
                    ->label('Filter Lokasi')
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value']) && $data['value'] !== '') {
                            return $query->whereHas('items', function ($q) use ($data) {
                                $q->where('qty_check', $data['value']);
                            });
                        }
                        return $query;
                    })
                    ->options([
                        '1' => 'Lokasi 1',
                        '2' => 'Lokasi 2',
                        '3' => 'Lokasi 3',
                        '4' => 'Lokasi 4',
                        '5' => 'Lokasi 5',
                        '6' => 'Lokasi 6',
                        '7' => 'Lokasi 7',
                        '8' => 'Lokasi 8',
                        '9' => 'Lokasi 9',
                        '10' => 'Lokasi 10',
                    ])
                    ->placeholder('Pilih Lokasi'),

                // Filter Tanggal
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereHas('items', function ($q) use ($date) {
                                    $q->whereDate('tanggal', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereHas('items', function ($q) use ($date) {
                                    $q->whereDate('tanggal', '<=', $date);
                                }),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['dari_tanggal'] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['sampai_tanggal'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Tables\Actions\Action::make('exportPdf')
                //     ->label('Export PDF')
                //     ->icon('heroicon-o-document-arrow-down')
                //     ->url(fn ($record) => route('laporan.operasional.pdf', $record->id))
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada data operasional')
            ->emptyStateDescription('Klik "Tambah Data Operasional" untuk membuat data baru.')
            ->emptyStateIcon('heroicon-o-clipboard-document');
    }

    // Override getTableQuery untuk custom filtering
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Apply layanan filter if exists
        if (isset($this->tableFilters['layanan']['value']) && $this->tableFilters['layanan']['value']) {
            $query->where('layanan_id', $this->tableFilters['layanan']['value']);
        }

        return $query;
    }
}

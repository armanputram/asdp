<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use App\Models\Perangkat;
use App\Models\Layanan;
use App\Models\Pelabuhan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Filament\Forms\Components\Radio;

class OperasionalResource extends Resource
{
    protected static ?string $model = Operasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilih cabang
                Forms\Components\Select::make('cabang_id')
                    ->relationship('cabang', 'nama')
                    ->required()
                    ->reactive(),

                // Pilih pelabuhan
                Forms\Components\Select::make('pelabuhan_id')
                    ->relationship('pelabuhan', 'nama')
                    ->required()
                    ->reactive(),

                // Pilih layanan (terfilter)
                Forms\Components\Select::make('layanan_id')
                    ->label('Layanan')
                    ->options(function (callable $get) {
                        $cabangId = $get('cabang_id');
                        $pelabuhanId = $get('pelabuhan_id');

                        if (!$cabangId || !$pelabuhanId) {
                            return [];
                        }

                        return Layanan::where('cabang_id', $cabangId)
                            ->where('pelabuhan_id', $pelabuhanId)
                            ->pluck('nama', 'id');
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $cabangId = $get('cabang_id');
                        $pelabuhanId = $get('pelabuhan_id');
                        $qtyCheck = $get('qty_check'); // Get the selected qty_check value

                        // Ambil perangkat berdasarkan cabang, pelabuhan, dan layanan
                        if ($cabangId && $pelabuhanId && $state) {
                            $perangkatList = Perangkat::where('cabang_id', $cabangId)
                                ->where('pelabuhan_id', $pelabuhanId)
                                ->where('layanan_id', $state)
                                ->get();

                            $items = [];
                            foreach ($perangkatList as $p) {
                                $items[] = [
                                    'perangkat_id' => $p->id,
                                    'nama' => $p->nama,
                                    'qty' => $p->qty,
                                    'qty_check' => $qtyCheck ?? 1, // Add qty_check field
                                    'status_perangkat' => null,
                                    'foto' => null,
                                    'catatan' => null,
                                    'tanggal' => now()->toDateString(),
                                    'waktu' => now()->format('H:i'),
                                ];
                            }
                            $set('items', $items);
                        }
                    }),

                // Select qty_check (renamed from lokasi)
                Forms\Components\Select::make('qty_check')
                    ->label('Titik Lokasi')
                    ->options([
                        1 => 'Lokasi 1',
                        2 => 'Lokasi 2',
                        3 => 'Lokasi 3',
                        4 => 'Lokasi 4',
                        5 => 'Lokasi 5',
                        6 => 'Lokasi 6',
                        7 => 'Lokasi 7',
                        8 => 'Lokasi 8',
                        9 => 'Lokasi 9',
                        10 => 'Lokasi 10',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Update existing items with the new qty_check value
                        $items = $get('items') ?? [];
                        foreach ($items as $key => $item) {
                            $items[$key]['qty_check'] = $state;
                        }
                        $set('items', $items);
                    }),

                // Repeater items
                Repeater::make('items')
                    ->schema([
                        Select::make('perangkat_id')
                            ->label('Nama Perangkat')
                            ->options(function (callable $get) {
                                $cabangId = $get('../../cabang_id');
                                $pelabuhanId = $get('../../pelabuhan_id');
                                $layananId = $get('../../layanan_id');

                                if (!$cabangId || !$pelabuhanId || !$layananId) {
                                    return [];
                                }

                                return Perangkat::where('cabang_id', $cabangId)
                                    ->where('pelabuhan_id', $pelabuhanId)
                                    ->where('layanan_id', $layananId)
                                    ->pluck('nama', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive(),

                        // Hidden field for qty_check - will be populated from parent
                        Forms\Components\Hidden::make('qty_check')
                            ->default(function (callable $get) {
                                return $get('../../qty_check') ?? 1;
                            }),

                       // DIGANTI DENGAN RADIO BUTTON
                        Radio::make('status_perangkat')
                            ->label('Status Perangkat')
                            ->options([
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->required(),

                        FileUpload::make('foto')
                            ->directory('operasionals')
                            ->image()
                            ->nullable()
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, callable $get): string {
                                $pelabuhanId = $get('../../pelabuhan_id');
                                $layananId = $get('../../layanan_id');
                                $perangkatId = $get('perangkat_id');
                                $qtyCheck = $get('qty_check') ?? $get('../../qty_check') ?? '1'; // Use qty_check as string

                                // Ambil tanggal dan waktu dari field form
                                $tanggal = $get('tanggal') ?? now()->toDateString();
                                $waktu = $get('waktu') ?? now()->format('H:i');

                                // Format tanggal dan waktu dalam format yang mudah dibaca
                                $datetime = "{$tanggal} {$waktu}";

                                // Ambil nama-nama dari database
                                $pelabuhan = Pelabuhan::find($pelabuhanId)?->nama ?? 'unknown';
                                $layanan = Layanan::find($layananId)?->nama ?? 'unknown';
                                $perangkat = Perangkat::find($perangkatId)?->nama ?? 'unknown';

                                // Bersihkan nama dari karakter yang tidak diinginkan
                                $pelabuhan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $pelabuhan);
                                $layanan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $layanan);
                                $perangkat = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $perangkat);
                                $datetime = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $datetime);

                                // Ambil extension file
                                $extension = $file->getClientOriginalExtension();

                                // Format: tanggal_waktu.pelabuhan.layanan.qty_check.perangkat.extension
                                return "{$datetime}.{$pelabuhan}.{$layanan}.{$qtyCheck}.{$perangkat}.{$extension}";
                            }),

                        Textarea::make('catatan'),

                        DatePicker::make('tanggal')->required(),
                        TimePicker::make('waktu')->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('cabang.nama')->label('Cabang')->searchable(),
                Tables\Columns\TextColumn::make('pelabuhan.nama')->label('Pelabuhan')->searchable(),
                Tables\Columns\TextColumn::make('layanan.nama')->label('Layanan')->searchable(),
                Tables\Columns\TextColumn::make('qty_check')
                    ->label('Titik Lokasi')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? "Lokasi {$firstItem->qty_check}" : 'Tidak ada data';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->tanggal->format('d/m/Y') : 'Tidak ada data';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->waktu->format('H:i') : 'Tidak ada data';
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // Tables\Actions\Action::make('exportPdf')
                //     ->label('Export PDF')
                //     ->icon('heroicon-o-document-arrow-down')
                //     ->url(fn (Model $record) => route('laporan.operasional.pdf', $record->id))
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperasionals::route('/'),
            'create' => Pages\CreateOperasional::route('/create'),
            'edit' => Pages\EditOperasional::route('/{record}/edit'),
        ];
    }
}

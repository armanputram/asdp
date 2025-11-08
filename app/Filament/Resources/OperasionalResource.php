<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use App\Models\Perangkat;
use App\Models\Layanan;
use App\Models\Pelabuhan;
use App\Models\CatatanPerangkat;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;

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
                    ->label('Cabang')
                    ->relationship('cabang', 'nama')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('pelabuhan_id', null);
                        $set('layanan_id', null);
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // Pilih pelabuhan (terfilter berdasarkan cabang)
                Forms\Components\Select::make('pelabuhan_id')
                    ->label('Pelabuhan')
                    ->options(function (callable $get) {
                        $cabangId = $get('cabang_id');

                        if (!$cabangId) {
                            return [];
                        }

                        return Pelabuhan::where('cabang_id', $cabangId)
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (callable $get) => !$get('cabang_id'))
                    ->placeholder('Pilih cabang terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('layanan_id', null);
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // Pilih layanan (terfilter berdasarkan cabang & pelabuhan)
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
                    ->searchable()
                    ->required()
                    ->disabled(fn (callable $get) => !$get('cabang_id') || !$get('pelabuhan_id'))
                    ->placeholder('Pilih cabang dan pelabuhan terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // Select qty_check (titik lokasi)
                Forms\Components\Select::make('qty_check')
                    ->label('Titik Lokasi')
                    ->options([
                        1 => 'Loket 1',
                        2 => 'Loket 2',
                        3 => 'Loket 3',
                        4 => 'Loket 4',
                        5 => 'Loket 5',
                        6 => 'Loket 6',
                        7 => 'Loket 7',
                        8 => 'Loket 8',
                        9 => 'Loket 9',
                        10 => 'Loket 10',
                    ])
                    ->required()
                    ->disabled(fn (callable $get) => !$get('cabang_id') || !$get('pelabuhan_id') || !$get('layanan_id'))
                    ->placeholder('Pilih cabang, pelabuhan, dan layanan terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $cabangId = $get('cabang_id');
                        $pelabuhanId = $get('pelabuhan_id');
                        $layananId = $get('layanan_id');

                        // Auto-populate items ketika qty_check dipilih
                        if ($cabangId && $pelabuhanId && $layananId && $state) {
                            $perangkatList = Perangkat::where('cabang_id', $cabangId)
                                ->where('pelabuhan_id', $pelabuhanId)
                                ->where('layanan_id', $layananId)
                                ->get();

                            $items = [];
                            foreach ($perangkatList as $p) {
                                // Cek apakah ada catatan aktif untuk perangkat ini
                                $catatanAktif = CatatanPerangkat::getCatatanAktif(
                                    $cabangId,
                                    $pelabuhanId,
                                    $layananId,
                                    $p->id,
                                    $state
                                );

                                $items[] = [
                                    'perangkat_id' => $p->id,
                                    'nama' => $p->nama,
                                    'qty' => $p->qty,
                                    'qty_check' => $state,
                                    'status_perangkat' => $p->status ?? 'baik',
                                    'foto' => null,
                                    'catatan' => $catatanAktif ? $catatanAktif->catatan : null, // Load catatan aktif
                                    'catatan_perangkat_id' => $catatanAktif ? $catatanAktif->id : null, // Simpan ID catatan
                                    'tanggal' => now()->toDateString(),
                                    'waktu' => now()->format('H:i'),
                                ];
                            }
                            $set('items', $items);
                        }
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
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $perangkat = Perangkat::find($state);

                                    if ($perangkat) {
                                        $set('status_perangkat', $perangkat->status ?? 'baik');
                                    }

                                    // Pastikan qty_check selalu sinkron dengan form utama
                                    $mainQtyCheck = $get('../../qty_check');
                                    if ($mainQtyCheck) {
                                        $set('qty_check', $mainQtyCheck);
                                    }

                                    // Load catatan aktif jika ada
                                    $cabangId = $get('../../cabang_id');
                                    $pelabuhanId = $get('../../pelabuhan_id');
                                    $layananId = $get('../../layanan_id');

                                    if ($cabangId && $pelabuhanId && $layananId && $mainQtyCheck) {
                                        $catatanAktif = CatatanPerangkat::getCatatanAktif(
                                            $cabangId,
                                            $pelabuhanId,
                                            $layananId,
                                            $state,
                                            $mainQtyCheck
                                        );

                                        if ($catatanAktif) {
                                            $set('catatan', $catatanAktif->catatan);
                                            $set('catatan_perangkat_id', $catatanAktif->id);
                                        }
                                    }
                                }
                            }),

                        // Hidden field untuk qty_check
                        Forms\Components\Hidden::make('qty_check')
                            ->default(function (callable $get) {
                                return $get('../../qty_check') ?? 1;
                            }),

                        // Hidden field untuk catatan_perangkat_id
                        Forms\Components\Hidden::make('catatan_perangkat_id'),

                        // Radio button status perangkat
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
                                $qtyCheck = $get('qty_check') ?? $get('../../qty_check') ?? '1';

                                $tanggal = $get('tanggal') ?? now()->toDateString();
                                $waktu = $get('waktu') ?? now()->format('H:i');
                                $datetime = "{$tanggal} {$waktu}";

                                $pelabuhan = Pelabuhan::find($pelabuhanId)?->nama ?? 'unknown';
                                $layanan = Layanan::find($layananId)?->nama ?? 'unknown';
                                $perangkat = Perangkat::find($perangkatId)?->nama ?? 'unknown';

                                $pelabuhan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $pelabuhan);
                                $layanan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $layanan);
                                $perangkat = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $perangkat);
                                $datetime = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $datetime);

                                $extension = $file->getClientOriginalExtension();

                                return "{$datetime}.{$pelabuhan}.{$layanan}.{$qtyCheck}.{$perangkat}.{$extension}";
                            }),

                        // Textarea catatan dengan info jika sudah ada catatan sebelumnya
                        Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->reactive()
                            ->helperText(function (callable $get) {
                                $catatanId = $get('catatan_perangkat_id');
                                if ($catatanId) {
                                    $catatan = CatatanPerangkat::find($catatanId);
                                    if ($catatan) {
                                        return '📌 Catatan aktif dari: ' . $catatan->created_at->format('d/m/Y H:i');
                                    }
                                }
                                return 'Catatan baru akan disimpan otomatis';
                            }),

                        // Toggle untuk menandai catatan selesai
                        Toggle::make('_mark_catatan_selesai')
                            ->label('Tandai catatan selesai')
                            ->helperText('Aktifkan jika masalah sudah teratasi')
                            ->visible(fn (callable $get) => !empty($get('catatan_perangkat_id')))
                            ->reactive(),

                        DatePicker::make('tanggal')->required()->default(now()),
                        TimePicker::make('waktu')->required()->default(now()),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Perangkat'),
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
                        return $firstItem ? "Loket {$firstItem->qty_check}" : 'Tidak ada data';
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use App\Models\Perangkat;
use App\Models\Layanan;
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
                            ->required(),

                        TextInput::make('qty_check')
                            ->label('Qty Check')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Select::make('status_perangkat')
                            ->options([
                                'bagus' => 'Bagus',
                                'rusak' => 'Rusak',
                            ])
                            ->required(),

                        FileUpload::make('foto')
                            ->directory('operasionals')
                            ->image()
                            ->nullable(),

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
                Tables\Columns\TextColumn::make('user.name')->label('User') ->searchable(),
                Tables\Columns\TextColumn::make('cabang.nama')->label('Cabang') ->searchable(),
                Tables\Columns\TextColumn::make('pelabuhan.nama')->label('Pelabuhan') ->searchable(),
                Tables\Columns\TextColumn::make('layanan.nama')->label('Layanan') ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                ])
                ->actions([
                    Tables\Actions\EditAction::make(),
                ])
                    ->headerActions([
                        Action::make('exportPdf')
                            ->label('Export PDF')
                            ->url(route('laporan.operasional.pdf'))
                            ->openUrlInNewTab(),

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

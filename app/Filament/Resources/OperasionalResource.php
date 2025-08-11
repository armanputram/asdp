<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use App\Models\Perangkat;
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
use Illuminate\Database\Eloquent\Model;


class OperasionalResource extends Resource
{
    protected static ?string $model = Operasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cabang_id')
                    ->relationship('cabang', 'nama')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('pelabuhan_id')
                    ->relationship('pelabuhan', 'nama')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('layanan_id')
                    ->relationship('layanan', 'nama')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Ambil semua perangkat berdasarkan layanan
                        $perangkatList = Perangkat::where('layanan_id', $state)->get();

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
                    }),

                Repeater::make('items')
                    ->schema([
                        Select::make('perangkat_id')
                            ->label('Nama Perangkat')
                            ->options(\App\Models\Perangkat::pluck('nama', 'id'))
                            ->searchable()
                            ->required(),

                        // TextInput::make('qty')
                        //     ->label('Qty (Master)')
                        //     ->numeric()
                        //     ->default(1)
                        //     ->required(),

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

                        DatePicker::make('tanggal')
                            ->required(),

                        TimePicker::make('waktu')
                            ->default(now()->format('H:i'))
                        ->required(),
                    ])

                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('cabang.nama')->label('Cabang'),
                Tables\Columns\TextColumn::make('pelabuhan.nama')->label('Pelabuhan'),
                Tables\Columns\TextColumn::make('layanan.nama')->label('Layanan'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
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

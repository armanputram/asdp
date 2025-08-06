<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LayananResource\Pages;
use App\Filament\Resources\LayananResource\RelationManagers;
use App\Models\Layanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;



class LayananResource extends Resource
{
    protected static ?string $model = Layanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Data Utama';

 public static function form(Form $form): Form
{
    return $form
        ->schema([

             Select::make('cabang_id')
                ->label('Cabang')
                ->relationship('cabang', 'nama')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('pelabuhan_id')
                ->label('Pelabuhan')
                ->relationship('pelabuhan', 'nama')
                ->searchable()
                ->preload()
                ->required(),


            TextInput::make('nama')
                ->label('Nama Layanan')
                ->required(),

            Repeater::make('perangkat')
                ->label('Daftar Perangkat')
                ->relationship()
                ->schema([
                    TextInput::make('nama')->label('Nama Perangkat')->required(),
                    TextInput::make('qty')->label('Jumlah')->numeric()->required(),
                ])
                ->collapsible()
                ->defaultItems(1)
                ->minItems(1)
                ->addActionLabel('Tambah Perangkat')
                ->columns(2),
        ]);
}
    public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('nama')
                ->label('Nama Layanan')
                ->sortable()
                ->searchable(),

             TextColumn::make('cabang.nama')
                ->label('Cabang'),

            TextColumn::make('pelabuhan.nama')
                ->label('Pelabuhan'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLayanans::route('/'),
            'create' => Pages\CreateLayanan::route('/create'),
            'edit' => Pages\EditLayanan::route('/{record}/edit'),
        ];
    }
}

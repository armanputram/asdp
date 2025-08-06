<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelabuhanResource\Pages;
use App\Models\Pelabuhan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class PelabuhanResource extends Resource
{
    protected static ?string $model = Pelabuhan::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Data Utama';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('cabang_id')
                    ->relationship('cabang', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Pelabuhan')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama Pelabuhan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('cabang.nama')->label('Cabang'),
                
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPelabuhans::route('/'),
            'create' => Pages\CreatePelabuhan::route('/create'),
            'edit' => Pages\EditPelabuhan::route('/{record}/edit'),
        ];
    }
}

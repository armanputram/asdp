<?php

namespace App\Filament\Resources;
use App\Models\Cabang;
use App\Models\Pelabuhan;
use App\Models\Layanan;
use App\Models\Perangkat;
use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;




class OperasionalResource extends Resource
{
    protected static ?string $model = Operasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Teknisi';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Hidden::make('user_id')
                ->default(auth()->id()),
            Select::make('cabang_id')
                ->label('Cabang')
                ->options(Cabang::all()->pluck('nama', 'id'))
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('pelabuhan_id', null)),

            Select::make('pelabuhan_id')
                ->label('Pelabuhan')
                ->options(fn (callable $get) =>
                    Pelabuhan::where('cabang_id', $get('cabang_id'))->pluck('nama', 'id'))
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('layanan_id', null)),

            Select::make('layanan_id')
                ->label('Jenis Layanan')
                ->options(fn (callable $get) =>
                    Layanan::where('pelabuhan_id', $get('pelabuhan_id'))->pluck('nama', 'id'))
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('perangkat_id', null)),

            Select::make('perangkat_id')
                ->label('Perangkat')
                ->options(fn (callable $get) =>
                    Perangkat::where('layanan_id', $get('layanan_id'))->pluck('nama', 'id'))
                ->required(),

            DatePicker::make('tanggal')->label('Tanggal')->required(),
            TimePicker::make('waktu')->label('Waktu')->required(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'bagus' => 'Bagus',
                    'rusak' => 'Rusak',
                ])
                ->required(),

            Textarea::make('catatan')->label('Catatan'),
           FileUpload::make('foto')
            ->label('Bukti Foto')
            ->image()
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //

            ])
            ->filters([
                //
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
            'index' => Pages\ListOperasionals::route('/'),
            'create' => Pages\CreateOperasional::route('/create'),
            'edit' => Pages\EditOperasional::route('/{record}/edit'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->can('view_any_operasional');
}

}

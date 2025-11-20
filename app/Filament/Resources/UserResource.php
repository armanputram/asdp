<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->hiddenOn('edit')
                    ->maxLength(255),

                Forms\Components\Select::make('Role')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),

                // Select Staff IT (Atasan)
                Forms\Components\Select::make('staff_it_id')
                    ->label('Staff IT (Atasan)')
                    ->relationship(
                        name: 'staffIt',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', function($q) {
                            $q->where('name', 'Staff IT');
                        })
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Pilih Staff IT yang menjadi atasan. Kosongkan jika user ini adalah Staff IT atau tidak punya atasan.')
                    ->columnSpanFull(),

                // Upload Tanda Tangan
                Forms\Components\FileUpload::make('signature')
                    ->label('Tanda Tangan')
                    ->image()
                    ->directory('signatures')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->helperText('Upload tanda tangan (PNG/JPG, max 2MB). Disarankan PNG dengan background transparan.')
                    ->imageEditor()
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get): string {
                        $name = $get('name') ?? 'user';
                        $name = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $name);
                        $extension = $file->getClientOriginalExtension();
                        return "ttd_{$name}.{$extension}";
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('staffIt.name')
                    ->label('Staff IT (Atasan)')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('petugasTi_count')
                    ->label('Jumlah Bawahan')
                    ->counts('petugasTi')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('has_signature')
                    ->label('Status TTD')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->signature))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filter Role')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('staff_it_id')
                    ->relationship('staffIt', 'name')
                    ->label('Filter Staff IT')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('is_staff_it')
                    ->label('Hanya Staff IT')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('roles', function($q) {
                            $q->where('name', 'Staff IT');
                        })
                    ),

                Tables\Filters\Filter::make('has_staff_it')
                    ->label('Punya Atasan (Staff IT)')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('staff_it_id')),

                Tables\Filters\Filter::make('has_signature')
                    ->label('Sudah Upload TTD')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('signature')),

                Tables\Filters\Filter::make('no_signature')
                    ->label('Belum Upload TTD')
                    ->query(fn (Builder $query): Builder => $query->whereNull('signature')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.resources.user.view-hierarchy', ['user' => $record]))
                    ->modalHeading(fn ($record) => 'Hierarki: ' . $record->name)
                    ->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

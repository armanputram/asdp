<?php

namespace App\Filament\Resources\PelabuhanResource\Pages;

use App\Filament\Resources\PelabuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPelabuhan extends EditRecord
{
    protected static string $resource = PelabuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

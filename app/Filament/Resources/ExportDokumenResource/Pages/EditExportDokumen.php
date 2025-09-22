<?php

namespace App\Filament\Resources\ExportDokumenResource\Pages;

use App\Filament\Resources\ExportDokumenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExportDokumen extends EditRecord
{
    protected static string $resource = ExportDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

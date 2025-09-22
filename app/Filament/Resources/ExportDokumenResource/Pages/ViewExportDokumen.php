<?php

namespace App\Filament\Resources\ExportDokumenResource\Pages;

use App\Filament\Resources\ExportDokumenResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExportDokumen extends ViewRecord
{
    protected static string $resource = ExportDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ExportDokumenResource\Pages;

use App\Filament\Resources\ExportDokumenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExportDokumens extends ListRecords
{
    protected static string $resource = ExportDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}

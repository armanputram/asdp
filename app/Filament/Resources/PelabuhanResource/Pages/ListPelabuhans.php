<?php

namespace App\Filament\Resources\PelabuhanResource\Pages;

use App\Filament\Resources\PelabuhanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelabuhans extends ListRecords
{
    protected static string $resource = PelabuhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

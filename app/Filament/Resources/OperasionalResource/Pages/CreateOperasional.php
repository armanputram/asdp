<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\OperasionalItem;
use Filament\Resources\Pages\CreateRecord;

class CreateOperasional extends CreateRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];
        unset($data['items']);
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->itemsData as $item) {
            $item['operasional_id'] = $this->record->id;
            OperasionalItem::create($item);
        }
    }
}

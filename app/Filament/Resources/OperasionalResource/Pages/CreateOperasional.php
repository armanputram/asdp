<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOperasional extends CreateRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Debug: lihat semua data yang masuk
        \Log::info('Form data received:', $data);

        // Ambil qty_check dari form utama
        $qtyCheck = $data['qty_check'] ?? '1';

        \Log::info('qty_check value from main form:', ['qty_check' => $qtyCheck]);

        // Hapus qty_check dari data utama karena tidak ada di tabel operasional
        unset($data['qty_check']);

        // Set user_id
        $data['user_id'] = auth()->id();

        // Create operasional record
        $record = static::getModel()::create($data);

        // Handle items
        if (isset($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                // Paksa qty_check menggunakan nilai dari form utama, bukan dari repeater
                $item['qty_check'] = $qtyCheck;
                $item['operasional_id'] = $record->id;

                \Log::info("Creating item {$index}:", [
                    'perangkat_id' => $item['perangkat_id'] ?? null,
                    'qty_check' => $item['qty_check'],
                    'original_qty_check' => $item['qty_check'] ?? 'not set'
                ]);

                // Create operasional item
                $record->items()->create($item);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

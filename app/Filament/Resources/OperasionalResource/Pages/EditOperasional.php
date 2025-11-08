<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\CatatanPerangkat;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOperasional extends EditRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load items dengan catatan
        if ($this->record) {
            $items = $this->record->items()->with('catatanPerangkat')->get()->toArray();

            // Ambil qty_check dari item pertama (semua item punya qty_check yang sama)
            if (!empty($items)) {
                $data['qty_check'] = $items[0]['qty_check'];
            }

            $data['items'] = $items;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Ambil qty_check dari form utama
        $qtyCheck = $data['qty_check'] ?? '1';

        // Hapus qty_check dari data utama
        unset($data['qty_check']);

        // Update operasional record (tanpa items)
        $itemsData = $data['items'] ?? [];
        unset($data['items']);

        $record->update($data);

        // Hapus items yang lama
        $record->items()->delete();

        // Re-create items dengan data baru
        if (!empty($itemsData)) {
            foreach ($itemsData as $item) {
                $item['qty_check'] = $qtyCheck;
                $item['operasional_id'] = $record->id;

                // Handle catatan
                if (!empty($item['catatan'])) {
                    $catatanId = $item['catatan_perangkat_id'] ?? null;
                    $markSelesai = $item['_mark_catatan_selesai'] ?? false;

                    if ($catatanId) {
                        $catatan = CatatanPerangkat::find($catatanId);

                        if ($catatan) {
                            $catatan->update([
                                'catatan' => $item['catatan'],
                            ]);

                            if ($markSelesai) {
                                $catatan->markAsSelesai();
                                $catatan->delete();
                            }

                            $item['catatan_perangkat_id'] = $catatan->id;
                        }
                    } else {
                        // Buat catatan baru
                        $newCatatan = CatatanPerangkat::create([
                            'cabang_id' => $record->cabang_id,
                            'pelabuhan_id' => $record->pelabuhan_id,
                            'layanan_id' => $record->layanan_id,
                            'perangkat_id' => $item['perangkat_id'],
                            'qty_check' => $qtyCheck,
                            'catatan' => $item['catatan'],
                            'is_selesai' => false,
                            'created_by' => auth()->id(),
                        ]);

                        $item['catatan_perangkat_id'] = $newCatatan->id;
                    }
                }

                unset($item['_mark_catatan_selesai']);

                $record->items()->create($item);
            }
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Operasional berhasil diupdate dan catatan tersimpan';
    }
}

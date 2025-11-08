<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\CatatanPerangkat;
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

        // Simpan data untuk catatan
        $cabangId = $data['cabang_id'];
        $pelabuhanId = $data['pelabuhan_id'];
        $layananId = $data['layanan_id'];

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

                // === TAMBAHAN: Handle Catatan Perangkat ===
                if (!empty($item['catatan'])) {
                    $catatanId = $item['catatan_perangkat_id'] ?? null;
                    $markSelesai = $item['_mark_catatan_selesai'] ?? false;

                    \Log::info("Processing catatan for item {$index}:", [
                        'catatan_id' => $catatanId,
                        'mark_selesai' => $markSelesai,
                        'catatan_text' => substr($item['catatan'], 0, 50),
                    ]);

                    if ($catatanId) {
                        // Update catatan yang sudah ada
                        $catatan = CatatanPerangkat::find($catatanId);

                        if ($catatan) {
                            // Update catatan
                            $catatan->update([
                                'catatan' => $item['catatan'],
                            ]);

                            \Log::info("Updated existing catatan:", ['id' => $catatanId]);

                            // Jika ditandai selesai, mark as selesai dan hapus (soft delete)
                            if ($markSelesai) {
                                $catatan->markAsSelesai();
                                $catatan->delete(); // Soft delete
                                \Log::info("Marked catatan as selesai:", ['id' => $catatanId]);
                            }

                            $item['catatan_perangkat_id'] = $catatan->id;
                        }
                    } else {
                        // Buat catatan baru (auto-save)
                        $newCatatan = CatatanPerangkat::create([
                            'cabang_id' => $cabangId,
                            'pelabuhan_id' => $pelabuhanId,
                            'layanan_id' => $layananId,
                            'perangkat_id' => $item['perangkat_id'],
                            'qty_check' => $qtyCheck,
                            'catatan' => $item['catatan'],
                            'is_selesai' => false,
                            'created_by' => auth()->id(),
                        ]);

                        $item['catatan_perangkat_id'] = $newCatatan->id;

                        \Log::info("Created new catatan:", [
                            'id' => $newCatatan->id,
                            'perangkat_id' => $item['perangkat_id'],
                        ]);
                    }
                }

                // Hapus field temporary yang tidak perlu disimpan
                unset($item['_mark_catatan_selesai']);
                // === END TAMBAHAN ===

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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Operasional berhasil dibuat dan catatan tersimpan otomatis';
    }
}

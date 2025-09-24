<?php

namespace App\Filament\Resources\ExportDokumenResource\Pages;

use App\Filament\Resources\ExportDokumenResource;
use App\Models\Operasional;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewExportDokumen extends ViewRecord
{
    protected static string $resource = ExportDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Dokumen Tergabung';
    }

    protected function getHeaderWidgets(): array
    {
        // Mendapatkan record utama yang sedang dilihat
        $mainRecord = $this->getRecord();

        // Mengambil semua record dari grup yang sama
        $groupedRecords = Operasional::where('pelabuhan_id', $mainRecord->pelabuhan_id)
            ->where('cabang_id', $mainRecord->cabang_id)
            ->where('user_id', $mainRecord->user_id)
            ->whereDate('created_at', $mainRecord->created_at)
            ->get();

        // Mengirimkan data yang dikelompokkan ke widget atau tampilan khusus
        // Contoh: return [ MyCustomWidget::make(['records' => $groupedRecords]) ];
        // Atau Anda bisa mengimplementasikan ini langsung di form schema
        return [];
    }
}

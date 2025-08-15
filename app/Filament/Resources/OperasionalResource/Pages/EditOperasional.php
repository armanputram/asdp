<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\OperasionalItem;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;

class EditOperasional extends EditRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->url(fn () => route('laporan.operasional.pdf', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil semua items terkait operasional ini
        $items = OperasionalItem::with('perangkat')
            ->where('operasional_id', $this->record->id)
            ->get();

        $data['items'] = $items->map(function ($item) {
            return [
                'id'               => $item->id, // Tambahkan ID untuk tracking
                'perangkat_id'     => $item->perangkat_id,
                'nama_perangkat'   => $item->perangkat->nama ?? '', // Ubah nama field
                'qty_check'        => $item->qty_check,
                'status_perangkat' => $item->status_perangkat,
                'foto'             => $item->foto,
                'catatan'          => $item->catatan,
                'tanggal'          => $item->tanggal,
                'waktu'            => $item->waktu,
            ];
        })->toArray();

        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('cabang_id')
                ->disabled()
                ->label('Cabang ID'),

            Forms\Components\TextInput::make('pelabuhan_id')
                ->disabled()
                ->label('Pelabuhan ID'),

            Forms\Components\TextInput::make('layanan_id')
                ->disabled()
                ->label('Layanan ID'),

            Forms\Components\Repeater::make('items')
                ->label('Items Operasional')
                ->schema([
                    Forms\Components\Hidden::make('id'), // Untuk tracking existing records

                    Forms\Components\Hidden::make('perangkat_id'),

                    Forms\Components\TextInput::make('nama_perangkat')
                        ->disabled()
                        ->label('Nama Perangkat'),

                    Forms\Components\TextInput::make('qty_check')
                        ->numeric()
                        ->required()
                        ->label('Qty Check')
                        ->minValue(0),

                    Forms\Components\Select::make('status_perangkat')
                        ->options([
                            'baik' => 'Baik',
                            'rusak' => 'Rusak',
                            'maintenance' => 'Maintenance',
                        ])
                        ->required()
                        ->label('Status Perangkat'),

                    Forms\Components\FileUpload::make('foto')
                        ->image()
                        ->directory('operasional-foto')
                        ->maxSize(2048) // 2MB
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->label('Foto'),

                    Forms\Components\Textarea::make('catatan')
                        ->rows(3)
                        ->columnSpanFull()
                        ->label('Catatan'),

                    Forms\Components\DatePicker::make('tanggal')
                        ->required()
                        ->label('Tanggal')
                        ->default(now()),

                    Forms\Components\TimePicker::make('waktu')
                        ->required()
                        ->label('Waktu')
                        ->default(now()),
                ])
                ->columns(2)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['nama_perangkat'] ?? 'Item Baru')
                ->addActionLabel('Tambah Item')
                ->reorderableWithButtons()
                ->cloneable(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Pastikan data yang tidak perlu dihapus dari save
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                // Hapus field yang tidak ada di database
                unset($item['nama_perangkat']);

                // Pastikan operasional_id diset
                $item['operasional_id'] = $this->record->id;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Handle manual saving jika diperlukan
        $this->handleItemsSync();
    }

    private function handleItemsSync(): void
    {
        $formData = $this->form->getState();

        if (isset($formData['items'])) {
            $existingItemIds = [];

            foreach ($formData['items'] as $itemData) {
                // Bersihkan data yang tidak diperlukan
                unset($itemData['nama_perangkat']);
                $itemData['operasional_id'] = $this->record->id;

                if (isset($itemData['id']) && $itemData['id']) {
                    // Update existing item
                    OperasionalItem::where('id', $itemData['id'])
                        ->update($itemData);
                    $existingItemIds[] = $itemData['id'];
                } else {
                    // Create new item
                    unset($itemData['id']);
                    $newItem = OperasionalItem::create($itemData);
                    $existingItemIds[] = $newItem->id;
                }
            }

            // Delete items that are no longer in the form
            OperasionalItem::where('operasional_id', $this->record->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
        }
    }
}

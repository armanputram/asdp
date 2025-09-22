<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\OperasionalItem;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;

class EditOperasional extends EditRecord
{
    protected static string $resource = OperasionalResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil semua items terkait operasional ini
        $items = OperasionalItem::with('perangkat')
            ->where('operasional_id', $this->record->id)
            ->get();

        // Ambil qty_check dari item pertama untuk ditampilkan di form utama
        $firstItem = $items->first();
        if ($firstItem) {
            $data['qty_check'] = $firstItem->qty_check;
        }

        $data['items'] = $items->map(function ($item) {
            return [
                'id'               => $item->id,
                'perangkat_id'     => $item->perangkat_id,
                'nama_perangkat'   => $item->perangkat->nama ?? '',
                'qty'              => $item->qty ?? 1, // PASTIKAN ADA DEFAULT VALUE
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

            Forms\Components\Select::make('qty_check')
                ->label('Titik Lokasi')
                ->options([
                    '1' => 'Lokasi 1',
                    '2' => 'Lokasi 2',
                    '3' => 'Lokasi 3',
                    '4' => 'Lokasi 4',
                    '5' => 'Lokasi 5',
                    '6' => 'Lokasi 6',
                    '7' => 'Lokasi 7',
                    '8' => 'Lokasi 8',
                    '9' => 'Lokasi 9',
                    '10' => 'Lokasi 10',
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $items = $get('items') ?? [];
                    if (!empty($items)) {
                        foreach ($items as $key => $item) {
                            $items[$key]['qty_check'] = $state;
                        }
                        $set('items', $items);
                    }
                }),

            Forms\Components\Repeater::make('items')
                ->label('Items Operasional')
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('perangkat_id'),

                    Forms\Components\TextInput::make('nama_perangkat')
                        ->disabled()
                        ->label('Nama Perangkat'),

                    // TAMBAHKAN FIELD QTY YANG BISA DIISI
                    Forms\Components\TextInput::make('qty')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->minValue(1)
                        ->label('Quantity'),

                    // Tampilkan qty_check sebagai field yang bisa dilihat
                    Forms\Components\TextInput::make('qty_check')
                        ->disabled()
                        ->label('Qty Check')
                        ->default(function (callable $get) {
                            return $get('../../qty_check') ?? '1';
                        }),

                    Forms\Components\Select::make('status_perangkat')
                        ->options([
                            'baik' => 'Baik',
                            'rusak' => 'Rusak',
                        ])
                        ->required()
                        ->label('Status Perangkat'),

                    Forms\Components\FileUpload::make('foto')
                        ->image()
                        ->directory('operasional-foto')
                        ->maxSize(2048)
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
                ->cloneable()
                ->deleteAction(
                    fn (Forms\Components\Actions\Action $action) => $action
                        ->requiresConfirmation()
                ),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan qty_check untuk nanti
        $this->qtyCheck = $data['qty_check'] ?? '1';

        // Hapus dari data utama karena tidak ada di tabel operasional
        unset($data['qty_check']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncItems();
    }

    private function syncItems(): void
    {
        $formData = $this->form->getState();
        $items = $formData['items'] ?? [];
        $qtyCheck = $this->qtyCheck ?? '1';

        $processedItemIds = [];

        foreach ($items as $itemData) {
            // Bersihkan data yang tidak diperlukan
            unset($itemData['nama_perangkat']);

            // Pastikan qty_check sesuai dengan pilihan di form utama
            $itemData['qty_check'] = $qtyCheck;
            $itemData['operasional_id'] = $this->record->id;

            // PASTIKAN QTY TIDAK NULL
            if (!isset($itemData['qty']) || $itemData['qty'] === null) {
                $itemData['qty'] = 1;
            }

            // PERBAIKAN LOGIKA UPDATE VS CREATE
            if (isset($itemData['id']) && !empty($itemData['id']) && is_numeric($itemData['id'])) {
                // Update existing item
                $itemId = $itemData['id'];
                unset($itemData['id']);

                // PASTIKAN RECORD BENAR-BENAR ADA
                $existingItem = OperasionalItem::find($itemId);
                if ($existingItem && $existingItem->operasional_id == $this->record->id) {
                    $existingItem->update($itemData);
                    $processedItemIds[] = $itemId;
                } else {
                    // Jika tidak ditemukan, buat baru
                    unset($itemData['id']);
                    $newItem = OperasionalItem::create($itemData);
                    $processedItemIds[] = $newItem->id;
                }
            } else {
                // Create new item
                unset($itemData['id']);
                $newItem = OperasionalItem::create($itemData);
                $processedItemIds[] = $newItem->id;
            }
        }

        // Hapus items yang tidak ada di form (hanya yang belongs to operasional ini)
        if (!empty($processedItemIds)) {
            OperasionalItem::where('operasional_id', $this->record->id)
                ->whereNotIn('id', $processedItemIds)
                ->delete();
        } else {
            // Jika tidak ada items, hapus semua items untuk operasional ini
            OperasionalItem::where('operasional_id', $this->record->id)->delete();
        }
    }
}

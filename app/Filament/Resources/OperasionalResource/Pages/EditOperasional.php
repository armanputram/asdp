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

        $data['items'] = $items->map(function ($item) {
            return [
                'perangkat_id'     => $item->perangkat_id,
                'nama'             => $item->perangkat->nama ?? '',
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
            Forms\Components\TextInput::make('cabang_id')->disabled(),
            Forms\Components\TextInput::make('pelabuhan_id')->disabled(),
            Forms\Components\TextInput::make('layanan_id')->disabled(),

            Forms\Components\Repeater::make('items')
                ->schema([
                    Forms\Components\Hidden::make('perangkat_id'),

                    Forms\Components\TextInput::make('nama')
                        ->disabled()
                        ->label('Nama Perangkat'),

                    Forms\Components\TextInput::make('qty_check')
                        ->numeric()
                        ->label('Qty Check'),

                    Forms\Components\Select::make('status_perangkat')
                        ->options([
                            'baik' => 'Baik',
                            'rusak' => 'Rusak',
                            'maintenance' => 'Maintenance',
                        ])
                        ->label('Status Perangkat'),

                    Forms\Components\FileUpload::make('foto')
                        ->image()
                        ->directory('operasional-foto')
                        ->label('Foto'),

                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan'),

                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal'),

                    Forms\Components\TimePicker::make('waktu')
                        ->label('Waktu'),
                ])
                ->columns(2),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportDokumenResource\Pages;
use App\Filament\Resources\ExportDokumenResource\RelationManagers;
use App\Models\Operasional;
use App\Models\Pelabuhan;
use App\Models\Layanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ExportDokumenResource extends Resource
{
    protected static ?string $model = Operasional::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Export Dokumen';
    protected static ?string $modelLabel = 'Export Dokumen';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cabang.nama')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelabuhan.nama')
                    ->label('Pelabuhan')
                    ->sortable()
                    ->badge()
                    ->color('info'),


                Tables\Columns\TextColumn::make('semua_layanan')
                    ->label('Layanan Tergabung')
                    ->sortable(false)
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        $totalLayanan = Layanan::where('pelabuhan_id', $record->pelabuhan_id)->count();
                        return "Semua Layanan ({$totalLayanan})";
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
                   ->filters([
            Tables\Filters\SelectFilter::make('cabang_id')
                ->label('Cabang')
                ->options(\App\Models\Cabang::all()->pluck('nama', 'id')),

            Tables\Filters\SelectFilter::make('pelabuhan_id')
                ->label('Pelabuhan')
                ->options(Pelabuhan::all()->pluck('nama', 'id')),

            Tables\Filters\SelectFilter::make('user_id')
                ->label('User')
                ->options(\App\Models\User::all()->pluck('name', 'id')),

            Tables\Filters\Filter::make('tanggal')
                ->form([
                    Forms\Components\DatePicker::make('dari_tanggal')
                        ->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('sampai_tanggal')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['dari_tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['sampai_tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['dari_tanggal']) {
                        $indicators['dari_tanggal'] = 'Dari: ' . \Carbon\Carbon::parse($data['dari_tanggal'])->format('d M Y');
                    }
                    if ($data['sampai_tanggal']) {
                        $indicators['sampai_tanggal'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai_tanggal'])->format('d M Y');
                    }
                    return $indicators;
                }),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),

      Action::make('export_pdf')
    ->label('Export PDF')
    ->icon('heroicon-o-document-arrow-down')
    ->color('success')
    ->url(fn (Operasional $record) => route('export.pdf', $record->id))
    ->openUrlInNewTab(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function ($records) {
                        return static::deleteGroupedRecords($records);
                    }),

                Tables\Actions\BulkAction::make('bulk_export_pdf')
                    ->label('Export PDF Terpilih')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($records) {
                        return static::generateBulkPDFGrouped($records);
                    })
                    ->requiresConfirmation()
                    ->modalDescription('Export semua grup dokumen terpilih ke dalam satu file PDF?'),
            ]),
        ])
        ->defaultSort('created_at', 'desc');
}


public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Informasi Utama')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('User')
                        ->disabled(),
                    Forms\Components\Select::make('cabang_id')
                        ->relationship('cabang', 'nama')
                        ->label('Cabang')
                        ->disabled(),
                    Forms\Components\Select::make('pelabuhan_id')
                        ->relationship('pelabuhan', 'nama')
                        ->label('Pelabuhan')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('created_at')
                        ->label('Tanggal & Waktu')
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Repeater::make('semua_layanan_dan_perangkat')
                ->label('Semua Layanan dan Perangkat')
                ->schema([
                    Forms\Components\Placeholder::make('nama_layanan')
                        ->label('Layanan')
                        ->content(fn ($state, $get) => \App\Models\Layanan::find($get('layanan_id'))->nama ?? 'Tidak Diketahui'),
                    Forms\Components\Placeholder::make('nama_perangkat')
                        ->label('Perangkat')
                        ->content(fn ($state, $get) => \App\Models\Perangkat::find($get('perangkat_id'))->nama ?? 'Tidak Diketahui'),
                    Forms\Components\Placeholder::make('lokasi')
                        ->label('Lokasi')
                        ->content(fn ($state, $get) => \App\Models\Pelabuhan::find($get('pelabuhan_id'))->nama ?? 'Tidak Diketahui'),
                    Forms\Components\Placeholder::make('status')
                        ->label('Status Laporan')
                        ->content(fn ($state, $get) => $get('status')),
                ])
                ->mutateDehydratedStateUsing(function (Model $record): array {
                    $allLayanan = \App\Models\Layanan::where('pelabuhan_id', $record->pelabuhan_id)->with('perangkat')->get();

                    $groupedRecords = \App\Models\Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                        ->whereDate('created_at', $record->created_at)
                        ->where('cabang_id', $record->cabang_id)
                        ->where('user_id', $record->user_id)
                        ->with('items')
                        ->get();

                    $data = [];
                    foreach ($allLayanan as $layanan) {
                        foreach ($layanan->perangkat as $perangkat) {
                            $isReported = false;
                            foreach ($groupedRecords as $opRecord) {
                                if ($opRecord->layanan_id == $layanan->id && $opRecord->items->where('perangkat_id', $perangkat->id)->isNotEmpty()) {
                                    $isReported = true;
                                    break;
                                }
                            }

                            $data[] = [
                                'layanan_id' => $layanan->id,
                                'perangkat_id' => $perangkat->id,
                                'pelabuhan_id' => $record->pelabuhan_id,
                                'status' => $isReported ? 'Sudah Dilaporkan' : 'Belum Dilaporkan',
                            ];
                        }
                    }

                    return $data;
                })
                ->columns(4)
                ->disableItemDeletion()
                ->disableItemCreation(),
        ]);
}
// ... (Sisa kode di bawahnya tetap sama)


    // Method untuk menampilkan detail grup record
    public static function viewGroupedRecord($record)


    {
        // Implementasi untuk menampilkan detail semua record dalam grup
        return null;
    }

    // Method untuk generate PDF dari grouped records
    public static function generatePDFGroupedRecords($record)
    {
        try {
            // Ambil semua record dalam grup tanggal dan pelabuhan yang sama
            $allRecords = Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                ->where('user_id', $record->user_id)
                ->where('cabang_id', $record->cabang_id)
                ->whereDate('created_at', $record->tanggal)
                ->get();

            // Ambil semua layanan dari pelabuhan
            $allLayanan = Layanan::where('pelabuhan_id', $record->pelabuhan_id)->get();

            // Ambil informasi dari record pertama
            $firstRecord = $allRecords->first();
            $pelabuhan = Pelabuhan::find($record->pelabuhan_id);
            $user = \App\Models\User::find($record->user_id);
            $cabang = \App\Models\Cabang::find($record->cabang_id);

            $pdf = PDF::loadView('pdf.export-grouped-layanan', [
                'pelabuhan' => $pelabuhan,
                'semua_layanan' => $allLayanan,
                'semua_dokumen' => $allRecords,
                'tanggal_export' => now(),
                'tanggal_dokumen' => $record->tanggal,
                'total_layanan' => $allLayanan->count(),
                'total_dokumen' => $allRecords->count(),
                'user' => $user,
                'cabang' => $cabang,
            ]);

            $filename = 'export-grouped-' .
                       str_replace(' ', '-', strtolower($pelabuhan->nama)) . '-' .
                       \Carbon\Carbon::parse($record->tanggal)->format('Y-m-d') . '-' .
                       now()->format('H-i-s') . '.pdf';

            Notification::make()
                ->title('PDF Dokumen Tergabung berhasil diexport')
                ->body($allRecords->count() . ' dokumen dengan ' . $allLayanan->count() . ' layanan dari ' . $pelabuhan->nama)
                ->success()
                ->duration(5000)
                ->send();

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saat export PDF grouped')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method untuk show group details
    public static function showGroupDetails($record)
    {
        // Method ini akan dipanggil oleh modal
        return null;
    }

    // Method untuk delete grouped records
    public static function deleteGroupedRecords($records)
    {
        try {
            DB::beginTransaction();

            $totalDeleted = 0;

            foreach ($records as $groupedRecord) {
                // Hapus semua record dalam grup
                $deletedCount = Operasional::where('pelabuhan_id', $groupedRecord->pelabuhan_id)
                    ->where('user_id', $groupedRecord->user_id)
                    ->where('cabang_id', $groupedRecord->cabang_id)
                    ->whereDate('created_at', $groupedRecord->tanggal)
                    ->delete();

                $totalDeleted += $deletedCount;
            }

            DB::commit();

            Notification::make()
                ->title('Dokumen berhasil dihapus')
                ->body($totalDeleted . ' dokumen telah dihapus')
                ->success()
                ->duration(5000)
                ->send();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Error saat menghapus dokumen')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method untuk bulk export PDF grouped
    public static function generateBulkPDFGrouped($records)
    {
        try {
            $allGroupData = [];

            foreach ($records as $groupedRecord) {
                // Ambil semua record dalam grup
                $groupRecords = Operasional::where('pelabuhan_id', $groupedRecord->pelabuhan_id)
                    ->where('user_id', $groupedRecord->user_id)
                    ->where('cabang_id', $groupedRecord->cabang_id)
                    ->whereDate('created_at', $groupedRecord->tanggal)
                    ->get();

                $layananList = Layanan::where('pelabuhan_id', $groupedRecord->pelabuhan_id)->get();
                $pelabuhan = Pelabuhan::find($groupedRecord->pelabuhan_id);
                $user = \App\Models\User::find($groupedRecord->user_id);
                $cabang = \App\Models\Cabang::find($groupedRecord->cabang_id);

                $allGroupData[] = [
                    'pelabuhan' => $pelabuhan,
                    'user' => $user,
                    'cabang' => $cabang,
                    'layanan' => $layananList,
                    'dokumen' => $groupRecords,
                    'tanggal' => $groupedRecord->tanggal,
                    'total_layanan' => $layananList->count(),
                    'total_dokumen' => $groupRecords->count(),
                ];
            }

            $pdf = PDF::loadView('pdf.export-bulk-grouped', [
                'group_data' => $allGroupData,
                'tanggal_export' => now(),
                'total_groups' => count($allGroupData),
                'total_semua_dokumen' => collect($allGroupData)->sum('total_dokumen'),
                'total_semua_layanan' => collect($allGroupData)->sum('total_layanan'),
            ]);

            $filename = 'export-bulk-grouped-' . now()->format('Y-m-d-H-i-s') . '.pdf';

            Notification::make()
                ->title('Bulk PDF Grouped berhasil diexport')
                ->body(count($allGroupData) . ' grup dengan total ' . collect($allGroupData)->sum('total_dokumen') . ' dokumen')
                ->success()
                ->duration(5000)
                ->send();

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saat bulk export PDF grouped')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method untuk memisahkan layanan yang sudah digabung
    public static function pisahkanLayanan(Operasional $record)
    {
        try {
            DB::beginTransaction();

            // Ambil semua layanan dari pelabuhan
            $layananList = Layanan::where('pelabuhan_id', $record->pelabuhan_id)->get();

            // Hapus record lama
            $originalData = $record->toArray();
            $record->delete();

            // Buat record baru untuk setiap layanan
            foreach ($layananList as $layanan) {
                Operasional::create([
                    'user_id' => $originalData['user_id'],
                    'cabang_id' => $originalData['cabang_id'],
                    'pelabuhan_id' => $originalData['pelabuhan_id'],
                    'layanan_id' => $layanan->id,
                    'created_at' => $originalData['created_at'],
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            Notification::make()
                ->title('Layanan berhasil dipisahkan')
                ->body('Layanan telah dipisah menjadi ' . $layananList->count() . ' dokumen terpisah')
                ->success()
                ->duration(5000)
                ->send();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Error saat memisahkan layanan')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method untuk bulk gabung per pelabuhan
    public static function gabungPerPelabuhan($records)
    {
        try {
            DB::beginTransaction();

            // Group records by pelabuhan_id
            $groupedByPelabuhan = collect($records)->groupBy('pelabuhan_id');

            $totalGabungan = 0;

            foreach ($groupedByPelabuhan as $pelabuhanId => $pelabuhanRecords) {
                // Ambil record pertama sebagai master
                $masterRecord = $pelabuhanRecords->first();

                // Update master record untuk gabung semua layanan
                $masterRecord->update([
                    'layanan_id' => null,
                    'updated_at' => now(),
                ]);

                // Hapus record lainnya dalam pelabuhan yang sama
                foreach ($pelabuhanRecords->slice(1) as $record) {
                    $record->delete();
                }

                $totalGabungan++;
            }

            DB::commit();

            Notification::make()
                ->title('Berhasil menggabungkan layanan per pelabuhan')
                ->body($totalGabungan . ' pelabuhan telah digabungkan layanannya')
                ->success()
                ->duration(5000)
                ->send();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Error saat menggabungkan per pelabuhan')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method untuk generate PDF semua layanan dari pelabuhan (sudah ada, diperbaiki)
// Method untuk generate PDF semua layanan dari pelabuhan (updated)
public static function generatePDFAllLayanan(Operasional $record)
{
    try {
        // Ambil semua layanan dari pelabuhan dengan perangkat
        $allLayanan = Layanan::with('perangkat')
            ->where('pelabuhan_id', $record->pelabuhan_id)
            ->get();

        // Ambil semua record operasional yang grouped dengan items
        $groupedRecords = Operasional::where('pelabuhan_id', $record->pelabuhan_id)
            ->whereDate('created_at', $record->created_at)
            ->where('cabang_id', $record->cabang_id)
            ->where('user_id', $record->user_id)
            ->with(['layanan', 'items.perangkat'])
            ->get();

        // Set data untuk template
        $tanggal = now()->format('l, F d, Y'); // Format: Wednesday, August 06, 2025
        $waktu = now()->format('H:i'); // Format: 10:00

        // Gunakan template operasional.blade.php yang sudah ada
        $pdf = PDF::loadView('pdf.operasional', [
            'operasional' => $record,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'pelabuhan' => $record->pelabuhan,
            'semua_layanan' => $allLayanan,
            'grouped_records' => $groupedRecords,
            'tanggal_export' => now(),
            'total_layanan' => $allLayanan->count(),
            'user' => $record->user,
            'cabang' => $record->cabang,
        ])->setPaper('a4', 'landscape'); // Set landscape sesuai template

        $filename = 'export-checklist-' .
                   str_replace(' ', '-', strtolower($record->pelabuhan->nama)) . '-' .
                   now()->format('Y-m-d-H-i-s') . '.pdf';

        Notification::make()
            ->title('PDF Checklist berhasil diexport')
            ->body('Form Checklist ' . $record->pelabuhan->nama . ' telah digenerate')
            ->success()
            ->duration(5000)
            ->send();

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    } catch (\Exception $e) {
        Notification::make()
            ->title('Error saat export PDF checklist')
            ->body($e->getMessage())
            ->danger()
            ->duration(10000)
            ->send();

        return null;
    }
}

    // Method untuk generate PDF normal (tambahan untuk layanan individu)
    public static function generatePDF(Operasional $record)
    {
        try {
            $pdf = PDF::loadView('pdf.export-layanan-individu', [
                'operasional' => $record,
                'pelabuhan' => $record->pelabuhan,
                'layanan' => $record->layanan,
                'user' => $record->user,
                'cabang' => $record->cabang,
                'tanggal_export' => now(),
            ]);

            $filename = 'export-' .
                       str_replace(' ', '-', strtolower($record->layanan->nama)) . '-' .
                       str_replace(' ', '-', strtolower($record->pelabuhan->nama)) . '-' .
                       now()->format('Y-m-d-H-i-s') . '.pdf';

            Notification::make()
                ->title('PDF Layanan berhasil diexport')
                ->body($record->layanan->nama . ' - ' . $record->pelabuhan->nama)
                ->success()
                ->duration(5000)
                ->send();

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saat export PDF')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();

            return null;
        }
    }

    // Method generatePDFWithItems, generateBulkPDF, dll tetap sama seperti sebelumnya
    // ...
 public static function getEloquentQuery(): Builder
{
    // Mengelompokkan data berdasarkan pelabuhan, cabang, user, dan tanggal
    return parent::getEloquentQuery()
        ->selectRaw('
            MIN(id) as id,
            pelabuhan_id,
            cabang_id,
            user_id,
            DATE(created_at) as tanggal,
            MIN(created_at) as created_at
        ')
        ->groupBy('pelabuhan_id', 'cabang_id', 'user_id', DB::raw('DATE(created_at)'));
}

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExportDokumens::route('/'),
            'create' => Pages\CreateExportDokumen::route('/create'),
            'view' => Pages\ViewExportDokumen::route('/{record}'),
            'edit' => Pages\EditExportDokumen::route('/{record}/edit'),
        ];
    }

    // Override untuk menambahkan navigation badge
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    // Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'pelabuhan.nama', 'layanan.nama', 'cabang.nama'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Operasional #' . $record->id . ' - ' . ($record->user->name ?? 'Unknown');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Pelabuhan' => $record->pelabuhan->nama ?? '-',
            'Layanan' => 'Semua Layanan Tergabung',
            'Cabang' => $record->cabang->nama ?? '-',
            'Tanggal' => $record->created_at->format('d M Y'),
        ];
    }

    // Custom method untuk statistik (disesuaikan untuk grouped data)
    public static function getStats(): array
    {
        // Hitung berdasarkan grup (tanggal + pelabuhan + user + cabang)
        $totalGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", user_id, "-", cabang_id)) as total')
            ->first()->total;

        $thisMonthGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", user_id, "-", cabang_id)) as total')
            ->whereMonth('created_at', now()->month)
            ->first()->total;

        $thisWeekGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", user_id, "-", cabang_id)) as total')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->first()->total;

        $totalRecords = Operasional::count();

        return [
            'total_groups' => $totalGroups,
            'total_records' => $totalRecords,
            'this_month' => $thisMonthGroups,
            'this_week' => $thisWeekGroups,
        ];
    }
}

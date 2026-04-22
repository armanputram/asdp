<?php

namespace App\Filament\Resources;
// use App\Filament\Resources\ExportDokumenResource\RelationManagers;
use App\Filament\Resources\ExportDokumenResource\Pages;
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
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\PDF;
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
                // User pertama yang buat operasional di pelabuhan + hari itu
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable(),

                // Semua petugas yang ikut mengisi di pelabuhan + hari yang sama
                Tables\Columns\TextColumn::make('semua_user')
                    ->label('Semua Petugas')
                    ->sortable(false)
                    ->getStateUsing(function ($record) {
                        return Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                            ->where('cabang_id', $record->cabang_id)
                            ->whereDate('created_at', $record->created_at)
                            ->with('user')
                            ->get()
                            ->pluck('user.name')
                            ->unique()
                            ->filter()
                            ->values()
                            ->implode(', ');
                    }),

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

                Tables\Columns\BadgeColumn::make('is_validated')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->is_validated ? 'Tervalidasi' : 'Menunggu Validasi')
                    ->colors([
                        'success' => 'Tervalidasi',
                        'warning' => 'Menunggu Validasi',
                    ])
                    ->icons([
                        'heroicon-o-check-badge' => 'Tervalidasi',
                        'heroicon-o-clock'       => 'Menunggu Validasi',
                    ])
                    ->tooltip(fn ($record) => $record->is_validated
                        ? 'Divalidasi oleh ' . optional($record->validatedBy)->name
                          . ' pada ' . optional($record->validated_at)->format('d M Y H:i')
                        : 'Belum divalidasi — Export PDF tidak tersedia'
                    ),

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

                Tables\Filters\SelectFilter::make('is_validated')
                    ->label('Status Validasi')
                    ->options([
                        '1' => 'Tervalidasi',
                        '0' => 'Menunggu Validasi',
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        isset($data['value']) && $data['value'] !== ''
                            ? $query->where('is_validated', $data['value'])
                            : $query
                    ),

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
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Operasional $record) => route('export.pdf', $record->id))
                    ->openUrlInNewTab(),

                Action::make('validasi')
                    ->label('Validasi')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Validasi Laporan')
                    ->modalDescription('Tanda tangan Anda akan muncul pada PDF setelah divalidasi.')
                    ->modalSubmitActionLabel('Ya, Validasi Sekarang')
                    ->visible(fn (Operasional $record) => ! $record->is_validated)
                    ->action(function (Operasional $record) {
                        // Validasi semua operasional di pelabuhan + cabang + hari yang sama
                        // mencakup semua user yang mengisi
                        Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                            ->where('cabang_id', $record->cabang_id)
                            ->whereDate('created_at', $record->created_at)
                            ->update([
                                'is_validated' => true,
                                'validated_by' => auth()->id(),
                                'validated_at' => now(),
                            ]);

                        Notification::make()
                            ->title('Laporan berhasil divalidasi')
                            ->body('Sekarang laporan dapat diekspor ke PDF.')
                            ->success()
                            ->send();
                    }),

                Action::make('batalkan_validasi')
                    ->label('Batalkan Validasi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Validasi')
                    ->modalDescription('Tanda tangan akan dihapus dan laporan tidak bisa diekspor sebelum divalidasi ulang.')
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    ->visible(fn (Operasional $record) => (bool) $record->is_validated)
                    ->action(function (Operasional $record) {
                        Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                            ->where('cabang_id', $record->cabang_id)
                            ->whereDate('created_at', $record->created_at)
                            ->update([
                                'is_validated' => false,
                                'validated_by' => null,
                                'validated_at' => null,
                            ]);

                        Notification::make()
                            ->title('Validasi dibatalkan')
                            ->body('Laporan perlu divalidasi ulang sebelum bisa diekspor.')
                            ->warning()
                            ->send();
                    }),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (Operasional $record) => (bool) $record->is_validated)
                    ->url(fn (Operasional $record) => route('export.pdf', $record->id))
                    ->openUrlInNewTab(),

                Action::make('export_pdf_locked')
                    ->label('Export PDF')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->visible(fn (Operasional $record) => ! $record->is_validated)
                    ->disabled()
                    ->tooltip('Validasi laporan terlebih dahulu sebelum export PDF'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
                            ->label('Dibuat Oleh (Pertama)')
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
                        // Ambil semua operasional di pelabuhan + cabang + hari (semua user)
                        $groupedRecords = \App\Models\Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                            ->where('cabang_id', $record->cabang_id)
                            ->whereDate('created_at', $record->created_at)
                            ->with('items')
                            ->get();

                        $allLayanan = \App\Models\Layanan::where('pelabuhan_id', $record->pelabuhan_id)
                            ->with('perangkat')
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
                                    'layanan_id'   => $layanan->id,
                                    'perangkat_id' => $perangkat->id,
                                    'pelabuhan_id' => $record->pelabuhan_id,
                                    'status'       => $isReported ? 'Sudah Dilaporkan' : 'Belum Dilaporkan',
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

    public static function viewGroupedRecord($record)
    {
        return null;
    }

    public static function generatePDFGroupedRecords($record)
    {
        try {
            $allRecords = Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                ->where('cabang_id', $record->cabang_id)
                ->whereDate('created_at', $record->tanggal)
                ->get();

            $allLayanan = Layanan::where('pelabuhan_id', $record->pelabuhan_id)->get();
            $pelabuhan  = Pelabuhan::find($record->pelabuhan_id);
            $cabang     = \App\Models\Cabang::find($record->cabang_id);

            $pdf = PDF::loadView('pdf.export-grouped-layanan', [
                'pelabuhan'       => $pelabuhan,
                'semua_layanan'   => $allLayanan,
                'semua_dokumen'   => $allRecords,
                'tanggal_export'  => now(),
                'tanggal_dokumen' => $record->tanggal,
                'total_layanan'   => $allLayanan->count(),
                'total_dokumen'   => $allRecords->count(),
                'cabang'          => $cabang,
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

    public static function showGroupDetails($record)
    {
        return null;
    }

    public static function deleteGroupedRecords($records)
    {
        try {
            DB::beginTransaction();

            $totalDeleted = 0;

            foreach ($records as $groupedRecord) {
                $deletedCount = Operasional::where('pelabuhan_id', $groupedRecord->pelabuhan_id)
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

    public static function generateBulkPDFGrouped($records)
    {
        try {
            $allGroupData = [];

            foreach ($records as $groupedRecord) {
                $groupRecords = Operasional::where('pelabuhan_id', $groupedRecord->pelabuhan_id)
                    ->where('cabang_id', $groupedRecord->cabang_id)
                    ->whereDate('created_at', $groupedRecord->tanggal)
                    ->get();

                $layananList = Layanan::where('pelabuhan_id', $groupedRecord->pelabuhan_id)->get();
                $pelabuhan   = Pelabuhan::find($groupedRecord->pelabuhan_id);
                $cabang      = \App\Models\Cabang::find($groupedRecord->cabang_id);

                $allGroupData[] = [
                    'pelabuhan'     => $pelabuhan,
                    'cabang'        => $cabang,
                    'layanan'       => $layananList,
                    'dokumen'       => $groupRecords,
                    'tanggal'       => $groupedRecord->tanggal,
                    'total_layanan' => $layananList->count(),
                    'total_dokumen' => $groupRecords->count(),
                ];
            }

            $pdf = PDF::loadView('pdf.export-bulk-grouped', [
                'group_data'          => $allGroupData,
                'tanggal_export'      => now(),
                'total_groups'        => count($allGroupData),
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

    public static function pisahkanLayanan(Operasional $record)
    {
        try {
            DB::beginTransaction();

            $layananList  = Layanan::where('pelabuhan_id', $record->pelabuhan_id)->get();
            $originalData = $record->toArray();
            $record->delete();

            foreach ($layananList as $layanan) {
                Operasional::create([
                    'user_id'      => $originalData['user_id'],
                    'cabang_id'    => $originalData['cabang_id'],
                    'pelabuhan_id' => $originalData['pelabuhan_id'],
                    'layanan_id'   => $layanan->id,
                    'created_at'   => $originalData['created_at'],
                    'updated_at'   => now(),
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

    public static function gabungPerPelabuhan($records)
    {
        try {
            DB::beginTransaction();

            $groupedByPelabuhan = collect($records)->groupBy('pelabuhan_id');
            $totalGabungan      = 0;

            foreach ($groupedByPelabuhan as $pelabuhanId => $pelabuhanRecords) {
                $masterRecord = $pelabuhanRecords->first();
                $masterRecord->update(['layanan_id' => null, 'updated_at' => now()]);

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

    public static function generatePDFAllLayanan(Operasional $record)
    {
        if (! $record->is_validated) {
            Notification::make()
                ->title('Export gagal')
                ->body('Laporan harus divalidasi terlebih dahulu sebelum dapat diekspor.')
                ->danger()
                ->send();

            return null;
        }

        try {
            $allLayanan = Layanan::with('perangkat')
                ->where('pelabuhan_id', $record->pelabuhan_id)
                ->get();

            // Ambil semua operasional di pelabuhan + cabang + hari (semua user)
            $groupedRecords = Operasional::where('pelabuhan_id', $record->pelabuhan_id)
                ->where('cabang_id', $record->cabang_id)
                ->whereDate('created_at', $record->created_at)
                ->with(['layanan', 'items.perangkat'])
                ->get();

            $checklistData = [];

            foreach ($allLayanan as $layanan) {
                $layananItems = [];

                foreach ($layanan->perangkat as $index => $perangkat) {
                    $operasionalItem = null;
                    foreach ($groupedRecords as $opRecord) {
                        if ($opRecord->layanan_id == $layanan->id) {
                            $operasionalItem = $opRecord->items->where('perangkat_id', $perangkat->id)->first();
                            if ($operasionalItem) break;
                        }
                    }

                    $qtyChecks = [0,0,0,0,0,0,0,0,0];
                    if ($operasionalItem && $operasionalItem->qty_check) {
                        $checkData = is_string($operasionalItem->qty_check)
                            ? json_decode($operasionalItem->qty_check, true)
                            : $operasionalItem->qty_check;

                        if (is_array($checkData)) {
                            for ($i = 0; $i < min(count($checkData), 9); $i++) {
                                $qtyChecks[$i] = (int)$checkData[$i];
                            }
                        }
                    }

                    $layananItems[] = [
                        'no'      => $index + 1,
                        'name'    => $perangkat->nama,
                        'qty'     => $operasionalItem ? $operasionalItem->qty : $perangkat->jumlah_default ?? '-',
                        'checks'  => $qtyChecks,
                        'desc'    => $operasionalItem ? $operasionalItem->keterangan : '',
                        'catatan' => $operasionalItem ? $operasionalItem->catatan : '',
                        'doc'     => $operasionalItem && $operasionalItem->foto
                            ? url('storage/' . $operasionalItem->foto)
                            : '',
                    ];
                }

                $checklistData[$layanan->nama] = $layananItems;
            }

            $carbonDate = \Carbon\Carbon::parse($record->created_at);
            $tanggal    = $carbonDate->locale('id')->isoFormat('dddd, MMMM DD, YYYY');
            $waktu      = $carbonDate->format('H:i');

            $validatedByUser = \App\Models\User::find($record->validated_by);

            $pdf = PDF::loadView('pdf.operasional', [
                'operasional'    => $record,
                'tanggal'        => $tanggal,
                'waktu'          => $waktu,
                'pelabuhan'      => $record->pelabuhan,
                'checklistData'  => $checklistData,
                'tanggal_export' => now(),
                'total_layanan'  => $allLayanan->count(),
                'user'           => $record->user,
                'cabang'         => $record->cabang,
                'is_validated'   => $record->is_validated,
                'validated_by'   => $validatedByUser,
                'validated_at'   => $record->validated_at,
            ])->setPaper('a4', 'landscape');

            $filename = 'checklist-' .
                str_replace(' ', '-', strtolower($record->pelabuhan->nama)) . '-' .
                $carbonDate->format('Y-m-d') . '.pdf';

            Notification::make()
                ->title('PDF Checklist berhasil diexport')
                ->body('Form Checklist ' . $record->pelabuhan->nama . ' - ' . $tanggal)
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

    public static function generatePDF(Operasional $record)
    {
        try {
            $pdf = PDF::loadView('pdf.export-layanan-individu', [
                'operasional'    => $record,
                'pelabuhan'      => $record->pelabuhan,
                'layanan'        => $record->layanan,
                'user'           => $record->user,
                'cabang'         => $record->cabang,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->selectRaw('
                MIN(id) as id,
                pelabuhan_id,
                cabang_id,
                MIN(user_id) as user_id,
                DATE(created_at) as tanggal,
                MIN(created_at) as created_at,
                MAX(is_validated) as is_validated,
                MAX(validated_by) as validated_by,
                MAX(validated_at) as validated_at
            ')
            // GROUP BY pelabuhan + cabang + tanggal — tanpa user_id
            // Semua user yang mengisi pelabuhan yang sama di hari yang sama = 1 baris
            ->groupBy('pelabuhan_id', 'cabang_id', DB::raw('DATE(created_at)'));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExportDokumens::route('/'),
            'create' => Pages\CreateExportDokumen::route('/create'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'pelabuhan.nama', 'layanan.nama', 'cabang.nama'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Operasional #' . $record->id . ' - ' . ($record->pelabuhan->nama ?? 'Unknown');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Pelabuhan' => $record->pelabuhan->nama ?? '-',
            'Layanan'   => 'Semua Layanan Tergabung',
            'Cabang'    => $record->cabang->nama ?? '-',
            'Tanggal'   => $record->created_at->format('d M Y'),
        ];
    }

    public static function getStats(): array
    {
        $totalGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", cabang_id)) as total')
            ->first()->total;

        $thisMonthGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", cabang_id)) as total')
            ->whereMonth('created_at', now()->month)
            ->first()->total;

        $thisWeekGroups = Operasional::selectRaw('COUNT(DISTINCT CONCAT(DATE(created_at), "-", pelabuhan_id, "-", cabang_id)) as total')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->first()->total;

        $totalRecords = Operasional::count();

        return [
            'total_groups'  => $totalGroups,
            'total_records' => $totalRecords,
            'this_month'    => $thisMonthGroups,
            'this_week'     => $thisWeekGroups,
        ];
    }
}

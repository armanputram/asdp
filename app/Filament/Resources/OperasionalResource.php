<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\Operasional;
use App\Models\Perangkat;
use App\Models\Layanan;
use App\Models\Pelabuhan;
use App\Models\CatatanPerangkat;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;

/**
 * Resource ini adalah "controller" ala Filament untuk mengelola data Operasional
 * (laporan pengecekan perangkat IT harian). Filament Resource otomatis
 * menyediakan halaman List, Create, dan Edit berdasarkan schema yang didefinisikan
 * di method form() dan table() di bawah.
 */
class OperasionalResource extends Resource
{
    // Model Eloquent yang direpresentasikan oleh Resource ini
    protected static ?string $model = Operasional::class;

    // Icon yang muncul di sidebar navigasi admin panel
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    /**
     * Mendefinisikan struktur FORM (dipakai saat Create & Edit data).
     * Form ini bersifat CASCADING / BERTINGKAT:
     * Cabang -> Pelabuhan -> Layanan -> Titik Lokasi (Loket) -> Daftar Perangkat.
     * Setiap pilihan di atas akan membatasi opsi pada field di bawahnya.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ============================
                // FIELD 1: Pilih Cabang
                // ============================
                Forms\Components\Select::make('cabang_id')
                    ->label('Cabang')
                    ->relationship('cabang', 'nama') // otomatis ambil data dari relasi Eloquent "cabang"
                    ->searchable()
                    ->preload()   // preload semua opsi agar dropdown langsung terisi tanpa perlu ketik dulu
                    ->required()
                    ->reactive()  // wajib supaya field lain bisa "mendengar" perubahan nilai field ini
                    // Saat cabang berubah, reset semua field turunan supaya
                    // data yang tidak relevan (pelabuhan/layanan lama) tidak tersisa
                    ->afterStateUpdated(function (callable $set) {
                        $set('pelabuhan_id', null);
                        $set('layanan_id', null);
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // ============================
                // FIELD 2: Pilih Pelabuhan (bergantung pada Cabang)
                // ============================
                Forms\Components\Select::make('pelabuhan_id')
                    ->label('Pelabuhan')
                    ->options(function (callable $get) {
                        // Ambil nilai cabang_id yang sedang dipilih di form (real-time)
                        $cabangId = $get('cabang_id');

                        // Kalau cabang belum dipilih, dropdown pelabuhan kosong
                        if (!$cabangId) {
                            return [];
                        }

                        // Filter pelabuhan hanya yang milik cabang tsb
                        return Pelabuhan::where('cabang_id', $cabangId)
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->required()
                    // Nonaktifkan field ini selama cabang belum dipilih
                    ->disabled(fn (callable $get) => !$get('cabang_id'))
                    ->placeholder('Pilih cabang terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        // Reset field turunan (layanan, titik lokasi, daftar perangkat)
                        $set('layanan_id', null);
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // ============================
                // FIELD 3: Pilih Layanan (bergantung pada Cabang + Pelabuhan)
                // ============================
                Forms\Components\Select::make('layanan_id')
                    ->label('Layanan')
                    ->options(function (callable $get) {
                        $cabangId = $get('cabang_id');
                        $pelabuhanId = $get('pelabuhan_id');

                        // Layanan hanya bisa dipilih kalau cabang & pelabuhan sudah dipilih
                        if (!$cabangId || !$pelabuhanId) {
                            return [];
                        }

                        return Layanan::where('cabang_id', $cabangId)
                            ->where('pelabuhan_id', $pelabuhanId)
                            ->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (callable $get) => !$get('cabang_id') || !$get('pelabuhan_id'))
                    ->placeholder('Pilih cabang dan pelabuhan terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Reset titik lokasi & daftar perangkat setiap layanan berganti
                        $set('qty_check', null);
                        $set('items', []);
                    }),

                // ============================
                // FIELD 4: Pilih Titik Lokasi / Loket (1-10)
                // Ini field KUNCI yang men-generate isi Repeater "items" secara otomatis
                // ============================
                Forms\Components\Select::make('qty_check')
                    ->label('Titik Lokasi')
                    ->options([
                        1 => 'Loket 1', 2 => 'Loket 2', 3 => 'Loket 3',
                        4 => 'Loket 4', 5 => 'Loket 5', 6 => 'Loket 6',
                        7 => 'Loket 7', 8 => 'Loket 8', 9 => 'Loket 9',
                        10 => 'Loket 10',
                    ])
                    ->required()
                    ->disabled(fn (callable $get) => !$get('cabang_id') || !$get('pelabuhan_id') || !$get('layanan_id'))
                    ->placeholder('Pilih cabang, pelabuhan, dan layanan terlebih dahulu')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $cabangId = $get('cabang_id');
                        $pelabuhanId = $get('pelabuhan_id');
                        $layananId = $get('layanan_id');

                        // Hanya jalan kalau semua filter di atas + loket sudah lengkap dipilih
                        if ($cabangId && $pelabuhanId && $layananId && $state) {

                            // Ambil SEMUA perangkat yang terdaftar untuk kombinasi
                            // cabang + pelabuhan + layanan tersebut
                            $perangkatList = Perangkat::where('cabang_id', $cabangId)
                                ->where('pelabuhan_id', $pelabuhanId)
                                ->where('layanan_id', $layananId)
                                ->get();

                            $items = [];

                            // Loop tiap perangkat untuk membangun baris item di Repeater
                            foreach ($perangkatList as $p) {

                                // Cek apakah perangkat ini punya CATATAN GANGGUAN
                                // yang masih AKTIF (belum selesai) untuk loket ini.
                                // Kalau ada, catatan lama otomatis dimuat ulang (auto-fill)
                                // supaya IT Support tidak perlu ketik ulang masalah yang sama.
                                $catatanAktif = CatatanPerangkat::getCatatanAktif(
                                    $cabangId, $pelabuhanId, $layananId, $p->id, $state
                                );

                                // Susun 1 baris data perangkat untuk dimasukkan ke Repeater
                                $items[] = [
                                    'perangkat_id' => $p->id,
                                    'nama' => $p->nama,
                                    'qty' => $p->qty,
                                    'qty_check' => $state,
                                    // Default status ambil dari master data perangkat,
                                    // fallback 'baik' kalau kolom status kosong
                                    'status_perangkat' => $p->status ?? 'baik',
                                    'foto' => null,
                                    // Auto-isi catatan lama jika ada catatan aktif
                                    'catatan' => $catatanAktif ? $catatanAktif->catatan : null,
                                    'catatan_perangkat_id' => $catatanAktif ? $catatanAktif->id : null,
                                    'tanggal' => now()->toDateString(),
                                    'waktu' => now()->format('H:i'),
                                ];
                            }

                            // Isi otomatis Repeater "items" dengan seluruh perangkat
                            // yang relevan -> user tidak perlu tambah manual satu-satu
                            $set('items', $items);
                        }
                    }),

                // ============================
                // REPEATER: Daftar Perangkat yang Dicek
                // Setiap "card" dalam repeater = 1 perangkat yang diperiksa
                // ============================
                Repeater::make('items')
                    ->schema([

                        // --- Dropdown nama perangkat (bisa diganti manual per baris) ---
                        Select::make('perangkat_id')
                            ->label('Nama Perangkat')
                            ->options(function (callable $get) {
                                // "../../" artinya naik 2 level dari field ini
                                // (dari dalam repeater item -> keluar ke form utama)
                                // untuk mengambil nilai cabang/pelabuhan/layanan yang dipilih di atas
                                $cabangId = $get('../../cabang_id');
                                $pelabuhanId = $get('../../pelabuhan_id');
                                $layananId = $get('../../layanan_id');

                                if (!$cabangId || !$pelabuhanId || !$layananId) {
                                    return [];
                                }

                                return Perangkat::where('cabang_id', $cabangId)
                                    ->where('pelabuhan_id', $pelabuhanId)
                                    ->where('layanan_id', $layananId)
                                    ->pluck('nama', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            // Kalau user mengganti perangkat secara manual di satu baris,
                            // sistem otomatis sinkronkan ulang status & catatan aktifnya
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $perangkat = Perangkat::find($state);

                                    if ($perangkat) {
                                        $set('status_perangkat', $perangkat->status ?? 'baik');
                                    }

                                    // Pastikan qty_check di baris ini tetap sinkron
                                    // dengan qty_check utama (loket yang dipilih di atas)
                                    $mainQtyCheck = $get('../../qty_check');
                                    if ($mainQtyCheck) {
                                        $set('qty_check', $mainQtyCheck);
                                    }

                                    $cabangId = $get('../../cabang_id');
                                    $pelabuhanId = $get('../../pelabuhan_id');
                                    $layananId = $get('../../layanan_id');

                                    // Cek ulang apakah perangkat yang baru dipilih ini
                                    // punya catatan gangguan aktif, lalu auto-isi
                                    if ($cabangId && $pelabuhanId && $layananId && $mainQtyCheck) {
                                        $catatanAktif = CatatanPerangkat::getCatatanAktif(
                                            $cabangId, $pelabuhanId, $layananId, $state, $mainQtyCheck
                                        );

                                        if ($catatanAktif) {
                                            $set('catatan', $catatanAktif->catatan);
                                            $set('catatan_perangkat_id', $catatanAktif->id);
                                        }
                                    }
                                }
                            }),

                        // Field tersembunyi, hanya untuk menyimpan nilai qty_check per baris
                        // (tidak tampil ke user, tapi ikut tersimpan ke database)
                        Forms\Components\Hidden::make('qty_check')
                            ->default(function (callable $get) {
                                return $get('../../qty_check') ?? 1;
                            }),

                        // Field tersembunyi untuk menyimpan referensi ID catatan gangguan
                        // yang sedang aktif terkait perangkat ini (kalau ada)
                        Forms\Components\Hidden::make('catatan_perangkat_id'),

                        // --- Radio button status perangkat: Baik / Rusak ---
                        Radio::make('status_perangkat')
                            ->label('Status Perangkat')
                            ->options([
                                'baik' => 'Baik',
                                'rusak' => 'Rusak',
                            ])
                            ->inline()          // pilihan ditampilkan sejajar horizontal
                            ->inlineLabel(false)
                            ->required(),

                        // --- Upload foto bukti kondisi perangkat ---
                        FileUpload::make('foto')
                            ->directory('operasionals') // folder penyimpanan di storage
                            ->image()
                            ->nullable()
                            ->extraInputAttributes(['capture' => 'environment']) // hanya terima file gambar
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, callable $get): string {
                                $pelabuhanId = $get('../../pelabuhan_id');
                                $layananId = $get('../../layanan_id');
                                $perangkatId = $get('perangkat_id');
                                $qtyCheck = $get('qty_check') ?? $get('../../qty_check') ?? '1';

                                $tanggal = $get('tanggal') ?? now()->toDateString();
                                $waktu = $get('waktu') ?? now()->format('H:i');
                                $datetime = "{$tanggal} {$waktu}";

                                // Ambil nama asli (bukan ID) untuk pelabuhan/layanan/perangkat
                                // supaya nama file lebih mudah dibaca manusia
                                $pelabuhan = Pelabuhan::find($pelabuhanId)?->nama ?? 'unknown';
                                $layanan = Layanan::find($layananId)?->nama ?? 'unknown';
                                $perangkat = Perangkat::find($perangkatId)?->nama ?? 'unknown';

                                // Bersihkan karakter yang tidak valid untuk nama file
                                // (spasi & simbol khusus diganti underscore)
                                $pelabuhan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $pelabuhan);
                                $layanan = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $layanan);
                                $perangkat = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $perangkat);
                                $datetime = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $datetime);

                                $extension = $file->getClientOriginalExtension();

                                // Hasil akhir contoh:
                                // 2025-07-23_14_30.Gilimanuk.Loket_Tiket.3.Printer.jpg
                                return "{$datetime}.{$pelabuhan}.{$layanan}.{$qtyCheck}.{$perangkat}.{$extension}";
                            }),

                        // --- Textarea catatan/keterangan gangguan perangkat ---
                        Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->reactive()
                            // Helper text dinamis: kalau ada catatan_perangkat_id aktif,
                            // tampilkan info kapan catatan itu pertama kali dibuat
                            ->helperText(function (callable $get) {
                                $catatanId = $get('catatan_perangkat_id');
                                if ($catatanId) {
                                    $catatan = CatatanPerangkat::find($catatanId);
                                    if ($catatan) {
                                        return '📌 Catatan aktif dari: ' . $catatan->created_at->format('d/m/Y H:i');
                                    }
                                }
                                return 'Catatan baru akan disimpan otomatis';
                            }),

                        // --- Toggle untuk menandai catatan lama sudah selesai ditangani ---
                        Toggle::make('_mark_catatan_selesai')
                            ->label('Tandai catatan selesai')
                            ->helperText('Aktifkan jika masalah sudah teratasi')
                            // Toggle ini HANYA muncul kalau baris ini memang
                            // sedang membawa referensi catatan_perangkat_id (ada masalah lama aktif)
                            ->visible(fn (callable $get) => !empty($get('catatan_perangkat_id')))
                            ->reactive(),

                        // --- Tanggal & waktu pengecekan (default: saat ini) ---
                        DatePicker::make('tanggal')->required()->default(now()),
                        TimePicker::make('waktu')->required()->default(now()),
                    ])
                    ->columns(2)      // tiap card repeater dibagi 2 kolom internal
                    ->grid(2)         // tampilkan 2 card repeater sejajar per baris (kiri-kanan)
                    ->defaultItems(0) // repeater kosong secara default (baru terisi via qty_check)
                    ->addActionLabel('Tambah Perangkat')
                    ->columnSpanFull(), // repeater full-width, tidak terbagi kolom dengan field lain
            ]);
    }

    /**
     * Mendefinisikan struktur TABEL (halaman List / index).
     * Menampilkan ringkasan tiap laporan Operasional dalam bentuk baris.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('cabang.nama')->label('Cabang')->searchable(),
                Tables\Columns\TextColumn::make('pelabuhan.nama')->label('Pelabuhan')->searchable(),
                Tables\Columns\TextColumn::make('layanan.nama')->label('Layanan')->searchable(),

                // Kolom "Titik Lokasi" tidak disimpan langsung di tabel Operasional,
                // jadi diambil dari item pertama pada relasi items()
                Tables\Columns\TextColumn::make('qty_check')
                    ->label('Titik Lokasi')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? "Loket {$firstItem->qty_check}" : 'Tidak ada data';
                    })
                    ->searchable(),

                // Sama halnya, tanggal & waktu diambil dari item pertama
                // karena field tanggal/waktu memang disimpan per item, bukan per laporan
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->tanggal->format('d/m/Y') : 'Tidak ada data';
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('waktu')
                    ->label('Waktu')
                    ->getStateUsing(function (Model $record) {
                        $firstItem = $record->items()->first();
                        return $firstItem ? $firstItem->waktu->format('H:i') : 'Tidak ada data';
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc') // laporan terbaru tampil paling atas
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(), // hapus banyak data sekaligus
            ]);
    }

    /**
     * Mendaftarkan halaman-halaman (routes) yang tersedia untuk Resource ini.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperasionals::route('/'),          // halaman daftar laporan
            'create' => Pages\CreateOperasional::route('/create'),  // halaman tambah laporan baru
            'edit' => Pages\EditOperasional::route('/{record}/edit'), // halaman edit laporan
        ];
    }
}

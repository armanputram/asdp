<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GilimanukSeeder extends Seeder
{
    public function run()
    {
        // Get existing Cabang Ketapang (jangan create baru)
        $cabang = DB::table('cabang')->where('nama', 'Ketapang')->first();

        if (!$cabang) {
            // Jika belum ada, baru create
            $cabangId = DB::table('cabang')->insertGetId([
                'nama' => 'Ketapang',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $cabangId = $cabang->id;
        }

        // Create Pelabuhan Gilimanuk
        $pelabuhanId = DB::table('pelabuhan')->insertGetId([
            'nama' => 'Gilimanuk',
            'cabang_id' => $cabangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LOKET PEJALAN KAKI
        $layananLoket = DB::table('layanan')->insertGetId([
            'nama' => 'LOKET PEJALAN KAKI',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatLoket = [
            ['nama' => 'Gate In Turnstille', 'qty' => 4, 'keterangan' => 'Gate in 1, 2, 3, 4'],
            ['nama' => 'POS', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'Printer', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'Cash Drawer', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'Reader Emoney', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'Reader E-KTP', 'qty' => 4, 'keterangan' => 'Terpasang 0, 4 perangkat disimpan di gudang'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'UPS', 'qty' => 4, 'keterangan' => 'Loket PNP 1, 2, 3'],
            ['nama' => 'Vending Machine', 'qty' => 4, 'keterangan' => 'VM 1, 2, 3, 4'],
            ['nama' => 'Passport Reader', 'qty' => 1, 'keterangan' => 'Terpasang di loket Pnp 1'],
            ['nama' => 'Mikrofon', 'qty' => 1, 'keterangan' => 'Terpasang di loket Pnp 1'],
        ];

        foreach ($perangkatLoket as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananLoket,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // TOLGATE (R4)
        $layananTolgateR4 = DB::table('layanan')->insertGetId([
            'nama' => 'TOLGATE (R4)',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatTolgateR4 = [
            ['nama' => 'Perangkat Barrier Gate', 'qty' => 5, 'keterangan' => 'Tolgate (1, 2, 3, 4), LCM (5)'],
            ['nama' => 'Perangkat Barrier Gate (Depan)', 'qty' => 4, 'keterangan' => 'Gate untuk Gantry Sensor Tolgate 1,2,3,4'],
            ['nama' => 'Optical Barrier (Depan)', 'qty' => 4, 'keterangan' => 'OB untuk Gantry Sensor Tolgate 1,2,3,4'],
            ['nama' => 'POS', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Printer', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Cash Drawer', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Reader Emoney', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Reader E-KTP', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'CCTV ANPR', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'CCTV Jenis Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'CCTV Jenis Panjang Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'EPC LCS', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Sensor Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Panel LCS', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'UPS', 'qty' => 4, 'keterangan' => 'Tolgate (1, 2, 3, 4)'],
            ['nama' => 'Display Tarif & Golongan', 'qty' => 4, 'keterangan' => 'tg4 perangkat di tarik'],
            ['nama' => 'Switch Unmanaged (5 Port/8 Port)', 'qty' => 4, 'keterangan' => 'Didalam Panel LCS Loket R4 1, 2, 3, 4'],
        ];

        foreach ($perangkatTolgateR4 as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananTolgateR4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // TOLGATE (R2)
        $layananTolgateR2 = DB::table('layanan')->insertGetId([
            'nama' => 'TOLGATE (R2)',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatTolgateR2 = [
            ['nama' => 'Perangkat Barrier Gate', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'POS', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Printer', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Cash Drawer', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Reader Emoney', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Reader E-KTP', 'qty' => 7, 'keterangan' => 'Terpasang 0, 7 Perangkat disimpan di gudang'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Panel LCS', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'UPS', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Display Tarif & Golongan', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'CCTV Capture Plat Number', 'qty' => 7, 'keterangan' => 'Motor 1, 2, 3, 4, 5, 6, 7'],
            ['nama' => 'Switch Unmanaged (5 Port/8 Port)', 'qty' => 7, 'keterangan' => 'Didalam Panel LCS Loket Motor 1, 2, 3, 4, 5, 6, 7'],
        ];

        foreach ($perangkatTolgateR2 as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananTolgateR2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // DERMAGA
        $layananDermaga = DB::table('layanan')->insertGetId([
            'nama' => 'DERMAGA',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatDermaga = [
            ['nama' => 'Handheld Dermaga', 'qty' => 4, 'keterangan' => 'MB4, LCM (1, 2, 3)'],
            ['nama' => 'Barrier Gate', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & 4'],
            ['nama' => 'Optical Barrier', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & 4'],
            ['nama' => 'Manless 200', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & 4'],
            ['nama' => 'Gate Bording Turnstille', 'qty' => 4, 'keterangan' => '2 di Gangway MB1 & 2 di Gangway MB2'],
        ];

        foreach ($perangkatDermaga as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananDermaga,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // RAK SERVER E-Ticketing
        $layananServer = DB::table('layanan')->insertGetId([
            'nama' => 'RAK SERVER E-Ticketing',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatServer = [
            ['nama' => 'Server Main', 'qty' => 1, 'keterangan' => '-'],
            ['nama' => 'Server Back Up', 'qty' => 1, 'keterangan' => '-'],
            ['nama' => 'Router', 'qty' => 2, 'keterangan' => 'Mikrotik CCR (1 Matot, 1 Backup ON)'],
            ['nama' => 'Switch', 'qty' => 3, 'keterangan' => 'Managed Switch Core FO'],
            ['nama' => 'UPS', 'qty' => 2, 'keterangan' => '6 KVA & 3 KVA'],
            ['nama' => 'Rack Server', 'qty' => 2, 'keterangan' => '1 Unit (T. 1,2M), 1 Unit (T.25CM)'],
            ['nama' => 'AC Standing', 'qty' => 1, 'keterangan' => '-'],
            ['nama' => 'AC Split (Backup)', 'qty' => 2, 'keterangan' => '-'],
        ];

        foreach ($perangkatServer as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananServer,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Seeder Gilimanuk berhasil dijalankan!');
        $this->command->info('📍 Cabang: Ketapang');
        $this->command->info('🚢 Pelabuhan: Gilimanuk');
        $this->command->info('📦 Total: 5 Layanan, 58 Perangkat');
    }
}

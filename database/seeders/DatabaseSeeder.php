<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('operasional_items')->truncate();
        DB::table('operasional')->truncate();
        DB::table('perangkat')->truncate();
        DB::table('layanan')->truncate();
        DB::table('pelabuhan')->truncate();
        DB::table('cabang')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Create Cabang
        $cabangId = DB::table('cabang')->insertGetId([
            'nama' => 'Ketapang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Pelabuhan
        $pelabuhanId = DB::table('pelabuhan')->insertGetId([
            'nama' => 'Ketapang',
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
            ['nama' => 'POS', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'Printer', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'Cash Drawer', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'Reader Emoney', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'Reader E-KTP', 'qty' => 2, 'keterangan' => 'Terpasang 0, 2 perangkat disimpan di gudang'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'UPS', 'qty' => 2, 'keterangan' => 'Loket 1a & 1b'],
            ['nama' => 'UPS VM', 'qty' => 5, 'keterangan' => 'VM 1, 2, 3, 4, 5'],
            ['nama' => 'Vending Machine', 'qty' => 5, 'keterangan' => 'VM 1, 2, 3, 4, 5'],
            ['nama' => 'Passport Reader', 'qty' => 2, 'keterangan' => 'Terpasang 1, 1 perangkat disimpan di gudang'],
            ['nama' => 'Mikrofon', 'qty' => 2, 'keterangan' => 'Loket 1, gudang 1'],
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

        // TOLGATE (R4, TRUCK, LCM)
        $layananTolgate = DB::table('layanan')->insertGetId([
            'nama' => 'TOLGATE (R4, TRUCK, LCM)',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatTolgate = [
            ['nama' => 'Perangkat Barrier Gate', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'Perangkat Barrier Gate (Depan)', 'qty' => 4, 'keterangan' => 'Gate untuk Gantry Sensor Tolgate 1,2,3,4'],
            ['nama' => 'Optical Barrier (Depan)', 'qty' => 4, 'keterangan' => 'OB untuk Gantry Sensor Tolgate 1,2,3,4'],
            ['nama' => 'POS', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'Printer', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'Cash Drawer', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'Reader Emoney', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'Reader E-KTP', 'qty' => 9, 'keterangan' => 'Terpasang 0, 9 Perangkat disimpan di gudang'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 9, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8), LCM (9)'],
            ['nama' => 'CCTV ANPR', 'qty' => 6, 'keterangan' => 'Truck (1, 2, 3, 4), KK (6,7)'],
            ['nama' => 'CCTV Jenis Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate 1,2,3,4'],
            ['nama' => 'CCTV Jenis Panjang Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate 1,2,3,4'],
            ['nama' => 'EPC LCS', 'qty' => 6, 'keterangan' => 'Truck (1, 2, 3, 4), KK (7,8)'],
            ['nama' => 'PC VCS', 'qty' => 4, 'keterangan' => 'Truck 1,2,3,4'],
            ['nama' => 'Sensor Kendaraan', 'qty' => 4, 'keterangan' => 'Tolgate 1,2,3,4'],
            ['nama' => 'Panel LCS', 'qty' => 8, 'keterangan' => 'Truck (1, 2, 3, 4), KK (5,6,7,8)'],
            ['nama' => 'UPS', 'qty' => 6, 'keterangan' => 'Truck (1, 2, 3, 4), KK (7,8)'],
            ['nama' => 'Display Tarif & Golongan', 'qty' => 6, 'keterangan' => 'Truck (1, 2, 3, 4), KK (6,7)'],
            ['nama' => 'Switch Unmanaged (5 Port/8 Port)', 'qty' => 8, 'keterangan' => 'di dalam panel LCS Truck (1, 2, 3, 4), KK (5,6,7,8)'],
        ];

        foreach ($perangkatTolgate as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananTolgate,
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
            ['nama' => 'Perangkat Barrier Gate', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'POS', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Printer', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Cash Drawer', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Reader Emoney', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Reader E-KTP', 'qty' => 8, 'keterangan' => 'Terpasang 0, 8 Perangkat disimpan di gudang'],
            ['nama' => 'Barcode / Qrcode Scanner', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Panel LCS', 'qty' => 6, 'keterangan' => 'Motor 1,2,3,4, Motor 5,6 (1 Panel, Motor 7,8 (1 Panel)'],
            ['nama' => 'UPS', 'qty' => 6, 'keterangan' => 'Motor 1,2,3,4, Motor 5,6 (1 Ups), Motor 7,8 (1 Ups)'],
            ['nama' => 'Display Tarif & Golongan', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'CCTV Capture Plat Number', 'qty' => 8, 'keterangan' => 'Motor 1,2,3,4,5,6,7,8'],
            ['nama' => 'Switch Unmanaged (5 Port/8 Port)', 'qty' => 6, 'keterangan' => 'di dlm Panel LCS Motor 1,2,3,4, Motor 5,6 (1 Panel, Motor 7,8 (1 Panel)'],
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

        // LOKET BULUSAN
        $layananBulusan = DB::table('layanan')->insertGetId([
            'nama' => 'LOKET BULUSAN',
            'cabang_id' => $cabangId,
            'pelabuhan_id' => $pelabuhanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perangkatBulusan = [
            ['nama' => 'Perangkat Barrier Gate', 'qty' => 2, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'POS', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Printer', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Cash Drawer', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Reader Emoney', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Reader E-KTP', 'qty' => 3, 'keterangan' => 'Terpasang 0, 3 Perangkat disimpan di gudang'],
            ['nama' => 'Barcode Scanner', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Panel LCS', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'Display Tarif & Golongan', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
            ['nama' => 'CCTV Capture Plat Number', 'qty' => 3, 'keterangan' => 'Terpasang 0, 3 Perangkat disimpan di gudang'],
            ['nama' => 'Switch Unmanaged (5 Port/8 Port)', 'qty' => 3, 'keterangan' => 'Terpasang 1 &2, Perangkat Tolgate 3 disimpan di gudang'],
        ];

        foreach ($perangkatBulusan as $p) {
            DB::table('perangkat')->insert([
                'nama' => $p['nama'],
                'qty' => $p['qty'],
                'keterangan' => $p['keterangan'],
                'cabang_id' => $cabangId,
                'pelabuhan_id' => $pelabuhanId,
                'layanan_id' => $layananBulusan,
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
            ['nama' => 'Handheld Dermaga', 'qty' => 4, 'keterangan' => 'LCM 1, 2, 3 & MB4'],
            ['nama' => 'Barrier Gate', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & Ponton'],
            ['nama' => 'Optical Barrier', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & Ponton'],
            ['nama' => 'Manless 200', 'qty' => 4, 'keterangan' => 'MB1, 2, 3 & Ponton'],
            ['nama' => 'Gate Bording Turnstille', 'qty' => 2, 'keterangan' => 'Gangway MB1 & MB2'],
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
            ['nama' => 'Router', 'qty' => 2, 'keterangan' => 'Mikrotik CCR (1 On, 1 Backup)'],
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

        $this->command->info('✅ Seeder berhasil dijalankan!');
        $this->command->info('📍 Cabang: Ketapang');
        $this->command->info('🚢 Pelabuhan: Ketapang');
        $this->command->info('📦 Total: 6 Layanan, 67 Perangkat');
    }
}



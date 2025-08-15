<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Checklist Kesiapan Perangkat Kesisteman TI E-Ticketing</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 5px;
            line-height: 1.1;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }
        .logo-cell {
            width: 80px;
            text-align: center;
            font-weight: bold;
            background: #ffffff;
        }
        .title-cell {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 4px;
        }
        .doc-info {
            width: 140px;
            font-size: 8px;
            line-height: 1.2;
            vertical-align: middle;
        }
        .info-header {
            text-align: center;
            font-weight: bold;
            background: #f0f0f0;
            font-size: 10px;
            padding: 4px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 8px;
            height: 18px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 1px 2px;
            text-align: center;
            font-size: 8px;
            vertical-align: middle;
        }
        .main-table th {
            background: #f0f0f0;
            font-weight: bold;
            height: 25px;
        }
        .section-header {
            background: #d0d0d0;
            font-weight: bold;
            font-size: 9px;
        }
        .no-col {
            width: 25px;
            text-align: center;
        }
        .perangkat-col {
            text-align: left;
            width: 140px;
            padding-left: 4px !important;
        }
        .qty-col {
            width: 30px;
            font-size: 7px;
        }
        .check-cols {
            width: 12px;
            font-size: 7px;
        }
        .keterangan-col {
            text-align: left;
            width: 180px;
            padding-left: 3px !important;
            font-size: 7px;
        }
        .catatan-col {
            text-align: left;
            width: 90px;
            padding-left: 3px !important;
            font-size: 7px;
        }
        .doc-col {
            text-align: left;
            width: 80px;
            padding-left: 3px !important;
            font-size: 7px;
        }
        .checkbox-mark {
            font-size: 10px;
            font-weight: bold;
        }
        tr {
            height: 18px;
        }
        .track-header {
            font-size: 7px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="2">
                <img src="{{ public_path('logo/ASDP_Logo_2023.png') }}" alt="Logo ASDP" style="height: 50px;">
            </td>
            <td class="title-cell">
                FORM CHECKLIST KESIAPAN PERANGKAT KESISTEMAN TI<br>
                E-TICKETING
            </td>
            <td class="doc-info">
                <strong>No. Dokumen:</strong> TIF-001-50.01<br>
                <strong>Revisi:</strong> 00<br>
                <strong>Berlaku Efektif:</strong> 01 Mei 2020<br>
                <strong>Halaman:</strong> 1 dari 1
            </td>
        </tr>
        <tr>
            <td colspan="2" class="info-header">
                INFORMASI DATA
            </td>
        </tr>
    </table>

    <!-- Info Data -->
    <table class="info-table">
        <tr>
            <td style="width: 8%; font-weight: bold;">Cabang</td>
            <td style="width: 17%;">{{ $operasional->cabang->nama ?? 'Ketapang' }}</td>
            <td style="width: 8%; font-weight: bold;">Pelabuhan</td>
            <td style="width: 17%;">{{ $operasional->pelabuhan->nama ?? 'Ketapang' }}</td>
            <td style="width: 8%; font-weight: bold;">Tanggal</td>
            <td style="width: 17%;">{{ $tanggal ?? 'Rabu, Agustus 06, 2023' }}</td>
            <td style="width: 8%; font-weight: bold;">Paket</td>
            <td style="width: 17%;">{{ $paket ?? '10:00' }}</td>
        </tr>
    </table>

    <!-- Section 1: Loket Penjualan Kaki -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">No</th>
                <th rowspan="2" class="perangkat-col">Perangkat</th>
                <th rowspan="2" class="qty-col">Qty<br>(Unit)</th>
                <th colspan="5" class="section-header">LOKET PENJUALAN KAKI</th>
                <th rowspan="2" class="keterangan-col">Keterangan</th>
                <th rowspan="2" class="catatan-col">Catatan</th>
                <th rowspan="2" class="doc-col">Dokumentasi</th>
            </tr>
            <tr>
                <th class="check-cols">1</th>
                <th class="check-cols">2</th>
                <th class="check-cols">3</th>
                <th class="check-cols">4</th>
                <th class="check-cols">5</th>
            </tr>
        </thead>
        <tbody>
            @php
            $loket_items = [
                ['no' => 1, 'name' => 'Gate In Turnstile', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Gate in 1,2,3,4', 'catatan' => '', 'doc' => ''],
                ['no' => 2, 'name' => 'POS', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => ''],
                ['no' => 3, 'name' => 'Printer', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => ''],
                ['no' => 4, 'name' => 'Cash Drawer', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => ''],
                ['no' => 5, 'name' => 'Reader Eticket', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => ''],
                ['no' => 6, 'name' => 'Reader E-KTP', 'qty' => 2, 'checks' => [0,0,0,0,0], 'desc' => 'Terpasang 0, 2 perangkat disimpan di gudang', 'catatan' => '', 'doc' => ''],
                ['no' => 7, 'name' => 'Barcode/Qrcode Scanner', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => 'https://bit.ly/cekdokumentasi'],
                ['no' => 8, 'name' => 'UPS', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Loket 1a & 1b', 'catatan' => '', 'doc' => ''],
                ['no' => 9, 'name' => 'CCTV VM', 'qty' => 5, 'checks' => [1,1,1,1,1], 'desc' => 'VM 1, 2, 3, 4, 5', 'catatan' => '', 'doc' => ''],
                ['no' => 10, 'name' => 'Vending Machine', 'qty' => 5, 'checks' => [1,1,1,1,1], 'desc' => 'VM 1, 2, 3, 4, 5', 'catatan' => '', 'doc' => ''],
                ['no' => 11, 'name' => 'Passport Reader', 'qty' => 2, 'checks' => [1,1,0,0,0], 'desc' => 'Terpasang 1, 1 perangkat disimpan di gudang', 'catatan' => '', 'doc' => ''],
                ['no' => 12, 'name' => 'Mikrofon', 'qty' => 2, 'checks' => [1,0,0,0,0], 'desc' => 'Loket 1, gudang 1', 'catatan' => '', 'doc' => '']
            ];
            @endphp

            @foreach($loket_items as $item)
            <tr>
                <td class="no-col">{{ $item['no'] }}</td>
                <td class="perangkat-col">{{ $item['name'] }}</td>
                <td class="qty-col">{{ $item['qty'] }}</td>
                @for($i = 0; $i < 5; $i++)
                    <td class="check-cols">{{ $item['checks'][$i] ? '✓' : '' }}</td>
                @endfor
                <td class="keterangan-col">{{ $item['desc'] }}</td>
                <td class="catatan-col">{{ $item['catatan'] }}</td>
                <td class="doc-col">{{ $item['doc'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Section 2: Tolgate -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="3" class="no-col">No</th>
                <th rowspan="3" class="perangkat-col">Perangkat</th>
                <th rowspan="3" class="qty-col">Qty<br>(Unit)</th>
                <th colspan="9" class="section-header">TOLGATE (JR4, TRI CH, LCM)</th>
                <th rowspan="3" class="keterangan-col">Keterangan</th>
                <th rowspan="3" class="catatan-col">Catatan</th>
                <th rowspan="3" class="doc-col">Dokumentasi</th>
            </tr>
            <tr>
                <th colspan="4" class="track-header">Track</th>
                <th colspan="4" class="track-header">KK</th>
                <th rowspan="2" class="track-header">LCM</th>
            </tr>
            <tr>
                <th class="check-cols">1</th>
                <th class="check-cols">2</th>
                <th class="check-cols">3</th>
                <th class="check-cols">4</th>
                <th class="check-cols">5</th>
                <th class="check-cols">6</th>
                <th class="check-cols">7</th>
                <th class="check-cols">8</th>
            </tr>
        </thead>
        <tbody>
            @php
            $tolgate_items = [
                ['no' => 1, 'name' => 'Perangkat Barrier Gate', 'qty' => 16, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 2, 'name' => 'Perangkat Barrier Gate (Dosen)', 'qty' => 4, 'checks' => [1,1,1,1,0,0,0,0,0], 'desc' => 'Gate untuk (entry) Sensor Tolgate 1,2,3,4', 'catatan' => '', 'doc' => ''],
                ['no' => 3, 'name' => 'Optical Barrier (Dosen)', 'qty' => 154, 'checks' => [1,1,0,1,0,0,0,0,0], 'desc' => 'Dhl untuk Sensor Sensor Tolgate 1,2,3,4', 'catatan' => 'Kabel Opt Putus (Dosen) (Tiket 101353)', 'doc' => ''],
                ['no' => 4, 'name' => 'POS', 'qty' => 9, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 5, 'name' => 'Printer', 'qty' => 9, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 6, 'name' => 'Cash Drawer', 'qty' => 9, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 7, 'name' => 'Reader Eticket', 'qty' => 9, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 8, 'name' => 'Reader E-KTP', 'qty' => 9, 'checks' => [0,0,0,0,0,0,0,0,0], 'desc' => 'Terpasang 0, 9 Perangkat disimpan di gudang', 'catatan' => '', 'doc' => ''],
                ['no' => 9, 'name' => 'Barcode/Qrcode Scanner', 'qty' => 9, 'checks' => [1,1,1,1,1,1,1,1,1], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8), LCM (9)', 'catatan' => '', 'doc' => ''],
                ['no' => 10, 'name' => 'CCTV ANPR', 'qty' => 6, 'checks' => [1,1,1,1,1,1,0,0,0], 'desc' => 'Track (1,2,3,4), KK (6,7)', 'catatan' => '', 'doc' => ''],
                ['no' => 11, 'name' => 'CCTV Jenis Kendaraan', 'qty' => 4, 'checks' => [1,1,1,1,0,0,0,0,0], 'desc' => 'Tolgate 1,2,3,4', 'catatan' => '', 'doc' => ''],
                ['no' => 12, 'name' => 'CCTV Jenis Panjang Kendaraan', 'qty' => 4, 'checks' => [1,1,1,1,0,0,0,0,0], 'desc' => 'Tolgate 1,2,3,4', 'catatan' => 'https://bit.ly/cekdokumen', 'doc' => ''],
                ['no' => 13, 'name' => 'EPC LCS', 'qty' => 6, 'checks' => [1,1,1,1,1,1,0,0,0], 'desc' => 'Track (1,2,3,4), KK (7,8)', 'catatan' => '', 'doc' => ''],
                ['no' => 14, 'name' => 'PC VCS', 'qty' => 4, 'checks' => [1,1,1,1,0,0,0,0,0], 'desc' => 'Track 1,2,3,4', 'catatan' => 'PC VCS (Tolgate 2 No Display, arul on perangkat di Ruang IT (Open Tiket 101351))', 'doc' => ''],
                ['no' => 15, 'name' => 'Sensor Kendaraan', 'qty' => 4, 'checks' => [1,1,1,1,0,0,0,0,0], 'desc' => 'Tolgate 1,2,3,4', 'catatan' => '', 'doc' => ''],
                ['no' => 16, 'name' => 'Panel LCS', 'qty' => 8, 'checks' => [1,1,1,1,1,1,1,1,0], 'desc' => 'Track (1,2,3,4), KK (5,6,7,8)', 'catatan' => '', 'doc' => ''],
                ['no' => 17, 'name' => 'UPS', 'qty' => 6, 'checks' => [1,1,1,0,1,1,0,0,0], 'desc' => 'Track (1,2,3,4), KK (7,8)', 'catatan' => 'UPS Tidak Bisa Backup (Open Tiket 101522) Perangkat di work bisa IT', 'doc' => ''],
                ['no' => 18, 'name' => 'Display Tarif & Golongan', 'qty' => 6, 'checks' => [1,1,1,1,1,1,0,0,0], 'desc' => 'Track (1,2,3,4), KK (6,7)', 'catatan' => 'Display Tarif Tidak munk, arul on perangkat di Gudang IT', 'doc' => ''],
                ['no' => 19, 'name' => 'Switch (Unmanaged [5 Port] Port)', 'qty' => 8, 'checks' => [1,1,1,1,1,1,1,1,0], 'desc' => 'di dalam panel LCS Track (1,2,3,4), KK (5,6,7,8)', 'catatan' => '', 'doc' => '']
            ];
            @endphp

            @foreach($tolgate_items as $item)
            <tr>
                <td class="no-col">{{ $item['no'] }}</td>
                <td class="perangkat-col">{{ $item['name'] }}</td>
                <td class="qty-col">{{ $item['qty'] }}</td>
                @for($i = 0; $i < 8; $i++)
                    <td class="check-cols">{{ $item['checks'][$i] ? '✓' : '' }}</td>
                @endfor
                <td class="check-cols">{{ $item['checks'][8] ? '✓' : '' }}</td>
                <td class="keterangan-col">{{ $item['desc'] }}</td>
                <td class="catatan-col">{{ $item['catatan'] }}</td>
                <td class="doc-col">{{ $item['doc'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Form Checklist Kesiapan Perangkat Kesisteman TI E-Ticketing</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 5px;
            line-height: 1.1;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
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
            width: 290px;
            font-size: 8px;
            line-height: 1.7;
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
        }
         .info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 10px;
            height: 1px;
        }
        .info-tables {
            width: 100%;
            border-collapse: collapse;
        }
        .info-tables td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 10px;
            height: 1px;
            background: #c6c6c6;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
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
            height: 1px;
        }
        .section-header {
            background: #d0d0d0;
            font-weight: bold;
            font-size: 9px;
        }
        .no-col {
            width: 10px;
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
            font-size: 10px;
            font-weight: bold;
        }
        .check-rusak {
            background-color: #ff0000 !important;
            color: #ffffff !important;
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
        .layanan-header {
            background: #c6c6c6;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            padding: 4px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="1">
                <img src="{{ public_path('logo/ASDP_Logo_2023.png') }}" alt="Logo ASDP" style="height: 50px;">
            </td>
            <td class="title-cell">
                FORM CHECKLIST KESIAPAN PERANGKAT KESISTEMAN TI<br>
                E-TICKETING
            </td>
            <td class="doc-info">
                <strong>No. Dokumen :</strong> TIF-401.00.01<br>
                <strong>Revisi:</strong> 00<br>
                <strong>Berlaku Efektif:</strong> 01 Mei 2020<br>
                <strong>Halaman:</strong> 1 dari 1
            </td>
        </tr>
    </table>

    <table class="info-tables">
        <tr>
            <td colspan="2" class="info-header">
                INFORMASI DATA
            </td>
        </tr>
    </table>

    <!-- Info Data -->
    <table class="info-table">
        <tr>
            <td style="width: 4%; font-weight: bold;">Cabang</td>
            <td style="width: 30%;">{{ $operasional->cabang->nama ?? '-' }}</td>
            <td style="width: 7%; font-weight: bold;">Tanggal</td>
            <td style="width: 7%;">{{ $tanggal }}</td>
        </tr>
    </table>
    <table class="info-table">
        <tr>
            <td style="width: 4%; font-weight: bold;">Pelabuhan</td>
            <td style="width: 30%;">{{ $operasional->pelabuhan->nama ?? '-' }}</td>
            <td style="width: 7%; font-weight: bold;">Pukul</td>
            <td style="width: 7%;">{{ $waktu }}</td>
        </tr>
    </table>

    {{-- Loop setiap layanan dari database --}}
    @if(isset($checklistData) && !empty($checklistData))
        {{-- Tabel Gabungan Semua Layanan --}}
        <table class="main-table">
            {{-- Foreach untuk setiap layanan --}}
            @foreach($checklistData as $layananNama => $items)
                {{-- Header Layanan --}}
                <tr>
                    <td colspan="15" class="layanan-header">
                        {{ strtoupper($layananNama) }}
                    </td>
                </tr>

                {{-- Header Tabel untuk setiap layanan --}}
                <tr>
                    <th rowspan="2" class="no-col">No</th>
                    <th rowspan="2" class="perangkat-col">Perangkat</th>
                    <th rowspan="2" class="qty-col">Qty<br>(Unit)</th>
                    <th colspan="9" class="section-header">Lokasi</th>
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
                    <th class="check-cols">6</th>
                    <th class="check-cols">7</th>
                    <th class="check-cols">8</th>
                    <th class="check-cols">9</th>
                </tr>

                {{-- Foreach untuk setiap item perangkat --}}
              @foreach($items as $index => $item)
<tr>
    <td class="no-col">{{ $index + 1 }}</td>
    <td class="perangkat-col">{{ $item['name'] }}</td>
    <td class="qty-col">{{ $item['qty'] }}</td>
    @foreach($item['checks'] as $checkIndex => $check)
        @php
            // Cek status untuk lokasi ini secara spesifik
            $isRusakDiLokasi = isset($item['status_per_lokasi'][$checkIndex])
                && in_array($item['status_per_lokasi'][$checkIndex], ['rusak']);
        @endphp
        <td class="check-cols {{ $check && $isRusakDiLokasi ? 'check-rusak' : '' }}">
            @if($check)
                @if($isRusakDiLokasi)
                    {!! '&#10005;' !!}
                @else
                    {!! '&#10003;' !!}
                @endif
            @endif
        </td>
    @endforeach
    <td class="keterangan-col">{{ $item['desc'] ?? '-' }}</td>
    <td class="catatan-col">{{ $item['catatan'] ?? '-' }}</td>
    <td class="doc-col">{{ $item['doc'] ? 'Ada' : '' }}</td>
</tr>
@endforeach

                {{-- Tambahan baris kosong setelah setiap layanan --}}
                @for($i = 0; $i < 2; $i++)
                <tr>
                    <td class="no-col">.</td>
                    <td class="perangkat-col"></td>
                    <td class="qty-col"></td>
                    @for($j = 0; $j < 9; $j++)
                        <td class="check-cols"></td>
                    @endfor
                    <td class="keterangan-col"></td>
                    <td class="catatan-col"></td>
                    <td class="doc-col"></td>
                </tr>
                @endfor
            @endforeach
        </table>
    @else
        <table class="info-tables" style="margin-top: 10px;">
            <tr>
                <td colspan="2" style="text-align: center; padding: 20px;">
                    Tidak ada data checklist untuk ditampilkan
                </td>
            </tr>
        </table>
    @endif

</body>
</html>

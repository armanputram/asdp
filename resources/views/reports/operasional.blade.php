<!DOCTYPE html>
<html>
<head>
    <title>Laporan Operasional</title>
    <style>
        @page { size: A4 landscape; margin: 20px; }
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2, h4 { margin: 0; padding: 0; }
        .meta-table td { border: none; text-align: left; padding: 2px 5px; font-size: 10px; }
    </style>
</head>
<body>

    <h2 style="text-align:center;">FORM CHECKLIST KESIAPAN PERANGKAT KESISTEMAN TI </h2>
    <h2 style="text-align:center;">E-TICKETING</h2>

    <br>

    {{-- Metadata --}}
    <table class="meta-table" style="margin-bottom: 10px;">
        <tr>
            <td><strong>Cabang:</strong> {{ $data->first()->cabang->nama ?? '-' }}</td>
            <td><strong>Tanggal:</strong> {{ now()->format('d-m-Y') }}</td>
            <td><strong>No Dokumen:</strong> 001/OPS/2025</td>
        </tr>
        <tr>
            <td><strong>Pelabuhan:</strong> {{ $data->first()->pelabuhan->nama ?? '-' }}</td>
            <td><strong>Jam:</strong> {{ now()->format('H:i') }}</td>
            <td><strong>Revisi:</strong> 00</td>
        </tr>
        <tr>
            <td><strong>Layanan:</strong> {{ $data->first()->layanan->nama ?? '-' }}</td>
            <td colspan="2"></td>
        </tr>
    </table>

    {{-- Tabel Data --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Perangkat</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Tanggal</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $op)
                @foreach($op->items as $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->perangkat->nama ?? '-' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->status_perangkat}}</td>
                        <td style="text-align: left;">{{ $item->catatan }}</td>
                        <td>{{ $item->tanggal }}</td>
                        <td>{{ $item->waktu }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </tabl

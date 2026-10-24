<!DOCTYPE html>
<html>
<head>
    <title>Laporan Inventaris Desa</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            display: none;
        }
        .data-table {
            width: 92%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #555;
            padding: 4px 6px;
        }
        .data-table th {
            background-color: #f1f1f1;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>DAFTAR ASET {{ $tahun }}</h1>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">No</th>
                <th width="52%">Nama</th>
                <th width="16%">Jumlah</th>
                <th width="20%">Sumber Dana</th>
            </tr>
        </thead>
        <tbody>
            @php
                $daftarAset = $inventaris
                    ->groupBy(fn ($item) => mb_strtoupper(trim($item->nama_barang)))
                    ->map(function ($items, $nama) {
                        return [
                            'nama' => $nama,
                            'jumlah' => $items->count(),
                            'sumber_dana' => '-',
                        ];
                    })
                    ->values();
            @endphp
            @forelse($daftarAset as $index => $aset)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $aset['nama'] }}</td>
                    <td class="text-center">{{ $aset['jumlah'] }}</td>
                    <td class="text-center">{{ $aset['sumber_dana'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data inventaris.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

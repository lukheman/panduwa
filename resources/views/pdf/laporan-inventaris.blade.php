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
                <th width="5%">No</th>
                <th width="14%">Kode Barang</th>
                <th width="24%">Nama Barang</th>
                <th width="8%">Jumlah</th>
                <th width="17%">Sumber Dana</th>
                <th width="17%">Nilai Aset (Rp)</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventaris as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="text-center">{{ $item->jumlah }}</td>
                    <td class="text-center">{{ $item->sumberDana?->nama ?? $item->pengeluaran?->sumberDana?->nama ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->nilai_aset, 0, ',', '.') }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data inventaris.</td>
                </tr>
            @endforelse
        </tbody>
        @if($inventaris->count() > 0)
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">TOTAL</th>
                    <th class="text-center">{{ $totalUnit }} unit</th>
                    <th colspan="2"></th>
                    <th class="text-right">Rp {{ number_format($totalAset, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>

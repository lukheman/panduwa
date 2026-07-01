<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penggunaan Dana Desa</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 3px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-space {
            height: 80px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN PENGGUNAAN DANA DESA</h1>
        <p>PEMERINTAH DESA WAINDAWULA</p>
        <p>KECAMATAN SIOMPU KABUPATEN BUTON SELATAN</p>
    </div>

    <div class="info">
        <table border="0">
            <tr>
                <td width="150"><strong>Tahun Anggaran</strong></td>
                <td width="10">:</td>
                <td>{{ $tahun }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>:</td>
                <td>{{ $tanggalCetak }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="35%">Keterangan</th>
                <th width="20%">Kegiatan</th>
                <th width="25%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($pengeluarans as $pengeluaran)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($pengeluaran->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $pengeluaran->keterangan ?: '-' }}</td>
                    <td>{{ $pengeluaran->kegiatan ? $pengeluaran->kegiatan->nama_kegiatan : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data pengeluaran pada tahun {{ $tahun }}.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">TOTAL PENGELUARAN</th>
                <th class="text-right">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer clearfix">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <p><strong>Kepala Desa</strong></p>
            <div class="signature-space"></div>
            <p>_______________________</p>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Inventaris Desa</title>
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
        .summary-box {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .summary-box td {
            padding: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN INVENTARIS DESA</h1>
        <p>PEMERINTAH DESA WAINDAWULA</p>
        <p>KECAMATAN SIOMPU KABUPATEN BUTON SELATAN</p>
    </div>

    <div class="info">
        <table border="0">
            <tr>
                <td width="150"><strong>Tahun</strong></td>
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

    <div class="summary-box">
        <table border="0" width="100%">
            <tr>
                <td width="25%"><strong>Total Aset</strong>: Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                <td width="25%"><strong>Baik</strong>: {{ $baik }}</td>
                <td width="25%"><strong>Rusak Ringan</strong>: {{ $rusakRingan }}</td>
                <td width="25%"><strong>Rusak Berat</strong>: {{ $rusakBerat }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Barang</th>
                <th width="20%">Lokasi</th>
                <th width="15%">Tgl Perolehan</th>
                <th width="10%">Kondisi</th>
                <th width="10%">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($inventaris as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->lokasi }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_perolehan)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ ucfirst($item->kondisi) }}</td>
                    <td class="text-right">{{ number_format($item->nilai_aset, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data inventaris.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">TOTAL NILAI ASET</th>
                <th class="text-right">{{ number_format($totalAset, 0, ',', '.') }}</th>
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

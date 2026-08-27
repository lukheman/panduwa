<!DOCTYPE html>
<html>
<head>
    <title>Laporan Realisasi Anggaran</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 16px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .bold {
            font-weight: bold;
        }
        .row-header {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        .row-sub-header {
            background-color: #f5f5f5;
            font-weight: bold;
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
        <h1>LAPORAN REALISASI PENGGUNAAN DANA DESA TAHUN ANGGRAN {{ $tahun }}</h1>
        <p>PEMERINTAH DESA WAINDAWULA</p>
        <p>KECAMATAN SIOMPU KABUPATEN BUTON SELATAN</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">NOMOR REK.</th>
                <th width="42%">URAIAN</th>
                <th width="17%">ANGGARAN<br>Rp.</th>
                <th width="17%">REALISASI<br>Rp.</th>
                <th width="16%">SISA<br>Rp.</th>
            </tr>
            <tr>
                <th class="text-center">1</th>
                <th class="text-center">2</th>
                <th class="text-center">3</th>
                <th class="text-center">4</th>
                <th class="text-center">5</th>
            </tr>
        </thead>
        <tbody>
            {{-- Pemasukan --}}
            <tr class="row-sub-header">
                <td class="text-center">1</td>
                <td class="bold">TOTAL PEMASUKAN (PENDAPATAN DESA)</td>
                <td class="text-right bold" colspan="2">{{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            {{-- BELANJA DESA Header --}}
            <tr class="row-header">
                <td></td>
                <td class="bold">BELANJA DESA (REALISASI ANGGARAN)</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            {{-- Loop per Kelompok --}}
            @php $noKelompok = 1; @endphp
            @foreach(\App\Enums\KelompokKegiatan::cases() as $kelompok)
                @php
                    $kegiatanKelompok = $kegiatans->get($kelompok->value ?? $kelompok->value) ?? collect();
                    $totalAnggaranKelompok = $kegiatanKelompok->sum('rencana_anggaran');
                    $totalRealisasiKelompok = $kegiatanKelompok->sum('realisasi_anggaran');
                    $sisaKelompok = $totalAnggaranKelompok - $totalRealisasiKelompok;
                @endphp

                {{-- Header Kelompok --}}
                <tr class="row-header">
                    <td class="text-center">{{ $noKelompok }}</td>
                    <td class="bold">{{ strtoupper($kelompok->getLabel()) }}</td>
                    <td class="text-right bold">{{ number_format($totalAnggaranKelompok, 0, ',', '.') }}</td>
                    <td class="text-right bold">{{ number_format($totalRealisasiKelompok, 0, ',', '.') }}</td>
                    <td class="text-right bold">{{ number_format($sisaKelompok, 0, ',', '.') }}</td>
                </tr>

                {{-- Loop Kegiatan dalam Kelompok --}}
                @php $noKegiatan = 1; @endphp
                @foreach($kegiatanKelompok as $kegiatan)
                    @php
                        $realisasi = $kegiatan->realisasi_anggaran ?? 0;
                        $sisa = $kegiatan->rencana_anggaran - $realisasi;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $noKelompok }}.{{ $noKegiatan }}</td>
                        <td>{{ $kegiatan->nama_kegiatan }}</td>
                        <td class="text-right">{{ number_format($kegiatan->rencana_anggaran, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($realisasi, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($sisa, 0, ',', '.') }}</td>
                    </tr>
                    @php $noKegiatan++; @endphp
                @endforeach

                @php $noKelompok++; @endphp
            @endforeach

            {{-- JUMLAH BELANJA DESA --}}
            <tr class="row-header">
                <td></td>
                <td class="bold">JUMLAH BELANJA DESA</td>
                <td class="text-right bold">{{ number_format($totalRencana, 0, ',', '.') }}</td>
                <td class="text-right bold">{{ number_format($totalRealisasi, 0, ',', '.') }}</td>
                <td class="text-right bold">{{ number_format($totalRencana - $totalRealisasi, 0, ',', '.') }}</td>
            </tr>

            {{-- SISA ANGGARAN --}}
            <tr class="row-sub-header">
                <td></td>
                <td class="bold">SISA ANGGARAN (PEMASUKAN - BELANJA)</td>
                <td class="text-right bold" colspan="2">{{ number_format($totalPemasukan - $totalRealisasi, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
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

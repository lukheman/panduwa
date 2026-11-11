<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Pelaksanaan APBDes</title>
    <style>
        @page { size: portrait; margin: 20px 18px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 10px; }
        .document { border: 1.5px solid #222; }
        .identity { padding: 13px 14px 10px; text-align: center; border-bottom: 1.5px solid #222; }
        .identity h1, .identity h2, .identity h3, .identity p { margin: 0; font-weight: bold; }
        .identity h1 { font-size: 15px; line-height: 1.45; }
        .identity h2 { font-size: 15px; line-height: 1.45; }
        .identity h3 { font-size: 14px; line-height: 1.45; }
        .identity p { font-size: 14px; line-height: 1.45; }
        .metadata { width: 100%; border-collapse: collapse; border-bottom: 1.5px solid #222; }
        .metadata td { padding: 8px 28px; font-size: 11px; font-weight: bold; }
        .metadata td:last-child { text-align: right; }
        .report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report thead { display: table-header-group; }
        .report th, .report td { border-right: 1px solid #333; border-bottom: 1px solid #555; }
        .report th:last-child, .report td:last-child { border-right: 0; }
        .report th { height: 30px; padding: 4px 5px; text-align: center; vertical-align: middle; font-size: 10px; }
        .report th small { display: block; font-size: 10px; }
        .report td { height: 21px; padding: 3px 7px; vertical-align: middle; }
        .report .code { width: 11%; padding-left: 8px; white-space: nowrap; }
        .report .description { width: 45%; }
        .report .money { width: 14.666%; text-align: right; white-space: nowrap; }
        .report .section td, .report .field td { font-weight: bold; }
        .report .section .description, .report .field .description { text-transform: uppercase; }
        .report .field td, .report .subfield td { font-weight: bold; text-decoration: underline; }
        .report .activity td, .report .type td { font-weight: bold; }
        .report .group td { font-weight: bold; font-style: italic; }
        .report .total td { font-weight: bold; border-top: 1.5px solid #222; }
        .report .indent-1 { padding-left: 25px; }
        .report .indent-2 { padding-left: 40px; }
        .report .indent-3 { padding-left: 55px; }
        .footer { padding: 12px 16px 16px; font-size: 9px; page-break-inside: avoid; }
        .footer table { width: 100%; border-collapse: collapse; }
        .footer td { width: 50%; vertical-align: top; }
        .footer .right { text-align: center; }
        .signature-space { height: 42px; }
        .signature-name { text-decoration: underline; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $format = fn ($value) => number_format((float) $value, 2, ',', '.');
        $pendapatanRows = $ringkasan['pendapatanRows'];
        $pendapatanAnggaran = $ringkasan['pendapatanAnggaran'];
        $pendapatanRealisasi = $ringkasan['pendapatanRealisasi'];
        $pendapatanLebih = $pendapatanAnggaran - $pendapatanRealisasi;
    @endphp

    <div class="document">
        <div class="identity">
            <h1>LAPORAN REALISASI PELAKSANAAN</h1>
            <h2>ANGGARAN PENDAPATAN DAN BELANJA DESA PER SUMBERDANA</h2>
            <h3>PEMERINTAH DESA WAINDAWULA</h3>
            <p>TAHUN ANGGARAN {{ $tahun }}</p>
        </div>

        <table class="metadata">
            <tr>
                <td>Sumberdana&nbsp;&nbsp;:&nbsp;&nbsp; {{ $sumberDana?->kode ?? 'DDS' }} {{ $sumberDana?->nama ?? 'Dana Desa (Dropping APBN)' }}</td>
                <td>Realisasi s.d {{ \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="report">
            <colgroup>
                <col class="code"><col class="description"><col class="money"><col class="money"><col class="money">
            </colgroup>
            <thead>
                <tr>
                    <th>KODE REK</th><th>URAIAN</th><th>ANGGARAN<small>(Rp)</small></th><th>REALISASI<small>(Rp)</small></th><th>LEBIH/(KURANG)<small>(Rp)</small></th>
                </tr>
                <tr><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th></tr>
            </thead>
            <tbody>
                <tr class="section"><td>4.</td><td class="description">PENDAPATAN</td><td></td><td></td><td></td></tr>
                <tr class="type"><td>4.2.</td><td class="description indent-1">Pendapatan Transfer</td><td class="money">{{ $format($pendapatanAnggaran) }}</td><td class="money">{{ $format($pendapatanRealisasi) }}</td><td class="money">{{ $format($pendapatanLebih) }}</td></tr>
                @forelse($pendapatanRows as $row)
                    <tr class="{{ $row['class'] }}">
                        <td>{{ $row['code'] }}</td><td class="description indent-2">{{ $row['description'] }}</td><td class="money">{{ $format($row['anggaran']) }}</td><td class="money">{{ $format($row['realisasi']) }}</td><td class="money">{{ $format($row['anggaran'] - $row['realisasi']) }}</td>
                    </tr>
                @empty
                    @php
                        $kodeSumber = $sumberDana?->kode ?? \App\Models\SumberDana::KODE_DANA_DESA;
                        $alokasiDanaDesa = $kodeSumber === \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA;
                    @endphp
                    <tr><td>{{ $alokasiDanaDesa ? '4.2.2.' : '4.2.1.' }}</td><td class="description indent-2">{{ $alokasiDanaDesa ? 'Alokasi Dana Desa' : 'Dana Desa' }}</td><td class="money">0,00</td><td class="money">0,00</td><td class="money">0,00</td></tr>
                @endforelse
                <tr class="total"><td></td><td class="description">JUMLAH PENDAPATAN</td><td class="money">{{ $format($pendapatanAnggaran) }}</td><td class="money">{{ $format($pendapatanRealisasi) }}</td><td class="money">{{ $format($pendapatanLebih) }}</td></tr>

                <tr class="section"><td>5.</td><td class="description">BELANJA</td><td></td><td></td><td></td></tr>
                @foreach($ringkasan['belanjaRows'] as $row)
                    <tr class="{{ $row['class'] }}">
                        <td>{{ $row['code'] }}</td><td class="description indent-{{ min($row['level'], 3) }}">{{ $row['description'] }}</td><td class="money">{{ $format($row['anggaran']) }}</td><td class="money">{{ $format($row['realisasi']) }}</td><td class="money">{{ $format($row['anggaran'] - $row['realisasi']) }}</td>
                    </tr>
                @endforeach
                <tr class="total"><td></td><td class="description">JUMLAH BELANJA</td><td class="money">{{ $format($ringkasan['belanjaAnggaran']) }}</td><td class="money">{{ $format($ringkasan['belanjaRealisasi']) }}</td><td class="money">{{ $format($ringkasan['belanjaAnggaran'] - $ringkasan['belanjaRealisasi']) }}</td></tr>
                <tr class="total"><td></td><td class="description">SURPLUS/(DEFISIT)</td><td class="money">{{ $format($ringkasan['surplusAnggaran']) }}</td><td class="money">{{ $format($ringkasan['surplusRealisasi']) }}</td><td class="money">{{ $format($ringkasan['surplusAnggaran'] - $ringkasan['surplusRealisasi']) }}</td></tr>

                <tr class="section"><td>6.</td><td class="description">PEMBIAYAAN</td><td></td><td></td><td></td></tr>
                <tr class="type"><td>6.1.</td><td class="description indent-1">Penerimaan Pembiayaan</td><td class="money">{{ $format($ringkasan['penerimaanAnggaran']) }}</td><td class="money">{{ $format($ringkasan['penerimaanRealisasi']) }}</td><td class="money">{{ $format($ringkasan['penerimaanAnggaran'] - $ringkasan['penerimaanRealisasi']) }}</td></tr>
                @foreach($ringkasan['pembiayaanPenerimaan'] as $item)
                    <tr><td>{{ $item->rekening?->kode }}</td><td class="description indent-2">{{ $item->uraian }}</td><td class="money">{{ $format($item->anggaran) }}</td><td class="money">{{ $format($item->realisasi) }}</td><td class="money">{{ $format($item->anggaran - $item->realisasi) }}</td></tr>
                @endforeach
                <tr class="type"><td>6.2.</td><td class="description indent-1">Pengeluaran Pembiayaan</td><td class="money">{{ $format($ringkasan['pengeluaranAnggaran']) }}</td><td class="money">{{ $format($ringkasan['pengeluaranRealisasi']) }}</td><td class="money">{{ $format($ringkasan['pengeluaranAnggaran'] - $ringkasan['pengeluaranRealisasi']) }}</td></tr>
                @foreach($ringkasan['pembiayaanPengeluaran'] as $item)
                    <tr><td>{{ $item->rekening?->kode }}</td><td class="description indent-2">{{ $item->uraian }}</td><td class="money">{{ $format($item->anggaran) }}</td><td class="money">{{ $format($item->realisasi) }}</td><td class="money">{{ $format($item->anggaran - $item->realisasi) }}</td></tr>
                @endforeach
                <tr class="total"><td></td><td class="description">PEMBIAYAAN NETTO</td><td class="money">{{ $format($ringkasan['nettoAnggaran']) }}</td><td class="money">{{ $format($ringkasan['nettoRealisasi']) }}</td><td class="money">{{ $format($ringkasan['nettoAnggaran'] - $ringkasan['nettoRealisasi']) }}</td></tr>
                <tr class="total"><td></td><td class="description">SISA LEBIH/(KURANG) PERHITUNGAN ANGGARAN (SiLPA)</td><td class="money">{{ $format($ringkasan['silpaAnggaran']) }}</td><td class="money">{{ $format($ringkasan['silpaRealisasi']) }}</td><td class="money">{{ $format($ringkasan['silpaAnggaran'] - $ringkasan['silpaRealisasi']) }}</td></tr>
            </tbody>
        </table>

        <div class="footer">
            <table>
                <tr><td>Sumber: Sistem PANDUWA</td><td class="right">WAINDAWULA, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td></tr>
                <tr><td></td><td class="right">KEPALA DESA</td></tr>
                <tr><td></td><td class="right signature-space"></td></tr>
                <tr><td></td><td class="right signature-name">SUDIRMAN</td></tr>
            </table>
        </div>
    </div>
</body>
</html>

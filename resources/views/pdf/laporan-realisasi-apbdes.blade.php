<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Pelaksanaan APBDes</title>
    <style>
        @page { size: A4 portrait; margin: 18px 18px 42px 18px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 9.2px; }
        .report-wrapper { border: 1.4px solid #111; }
        .identity { padding: 11px 12px 9px; text-align: center; border-bottom: 1.4px solid #111; }
        .identity h1, .identity h2, .identity h3, .identity p { margin: 0; font-weight: bold; line-height: 1.42; }
        .identity h1 { font-size: 13.5px; }
        .identity h2 { font-size: 13px; }
        .identity h3 { font-size: 13px; }
        .identity p { font-size: 13px; }
        .signature { width: 42%; margin: 24px 0 0 auto; text-align: center; page-break-inside: avoid; }
        .signature .space { height: 58px; }
        .signature p { margin: 3px 0; }
        .report .indent-4 { padding-left: 50px; }
        .metadata { width: 100%; border-collapse: collapse; border-bottom: 1.4px solid #111; }
        .metadata td { padding: 7px 30px; font-size: 10px; font-weight: bold; }
        .metadata td:last-child { text-align: right; }
        .report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report thead { display: table-header-group; }
        .report th, .report td { border-right: 1px solid #666; border-bottom: 1px solid #666; }
        .report th:last-child, .report td:last-child { border-right: 0; }
        .report th { height: 27px; padding: 3px 4px; text-align: center; vertical-align: middle; font-size: 9.2px; font-weight: bold; }
        .report th small { display: block; font-size: 9px; font-weight: bold; }
        .report td { height: 18px; padding: 2px 5px; vertical-align: middle; }
        .report th.code, .report td.code { width: 10%; padding-left: 6px; white-space: nowrap; }
        .report th.description, .report td.description { width: 45%; }
        .report th.money, .report td.money { width: 15%; text-align: right; white-space: nowrap; padding-right: 7px; }
        .report .section td { font-weight: bold; text-transform: uppercase; }
        .report .field td { font-weight: bold; text-transform: uppercase; }
        .report .subfield td { font-weight: bold; }
        .report .subfield .description { font-style: italic; text-decoration: underline; }
        .report .activity td { font-weight: normal; }
        .report .type td { font-weight: bold; }
        .report .group td { font-weight: bold; font-style: italic; }
        .report .total td { font-weight: bold; border-top: 1.4px solid #111; }
        .report .total .description { text-align: center; }
        .report .indent-1 { padding-left: 14px; }
        .report .indent-2 { padding-left: 26px; }
        .report .indent-3 { padding-left: 38px; }
    </style>
</head>
<body>
    @php
        $format = function ($value): string {
            $value = (float) $value;

            return $value < 0
                ? '('.number_format(abs($value), 2, ',', '.').')'
                : number_format($value, 2, ',', '.');
        };
        $kode = fn ($value): string => filled($value) ? rtrim((string) $value, '.').'.' : '';
        $pendapatanRows = $ringkasan['pendapatanRows'];
        $pendapatanAnggaran = $ringkasan['pendapatanAnggaran'];
        $pendapatanRealisasi = $ringkasan['pendapatanRealisasi'];
        $pendapatanLebih = $pendapatanAnggaran - $pendapatanRealisasi;

        $kodeSumber = $sumberDana?->kode ?? \App\Models\SumberDana::KODE_DANA_DESA;
        $namaSumber = $sumberDana?->nama ?? 'Dana Desa';
        $labelSumber = trim($kodeSumber.' '.$namaSumber);

        if ($kodeSumber === \App\Models\SumberDana::KODE_DANA_DESA && ! str_contains(strtolower($labelSumber), 'dropping')) {
            $labelSumber .= ' (Dropping APBN)';
        } elseif ($kodeSumber === \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA && ! str_contains(strtolower($labelSumber), 'apbd')) {
            $labelSumber .= ' (APBD)';
        }
    @endphp

    <div class="report-wrapper">
        <div class="identity">
            <h1>LAPORAN REALISASI PELAKSANAAN</h1>
            <h2>ANGGARAN PENDAPATAN DAN BELANJA DESA PER SUMBERDANA</h2>
            <h3>PEMERINTAH DESA WAINDAWULA</h3>
            <p>TAHUN ANGGARAN {{ $tahun }}</p>
        </div>

        <table class="metadata">
            <tr>
                <td>Sumberdana&nbsp;&nbsp;:&nbsp;&nbsp; {{ $labelSumber }}</td>
                <td>Realisasi s.d {{ \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="report">
            <thead>
                <tr>
                    <th class="code" style="width: 10%;">KODE REK</th>
                    <th class="description" style="width: 45%;">URAIAN</th>
                    <th class="money" style="width: 15%;">ANGGARAN<small>(Rp)</small></th>
                    <th class="money" style="width: 15%;">REALISASI<small>(Rp)</small></th>
                    <th class="money" style="width: 15%;">LEBIH/(KURANG)<small>(Rp)</small></th>
                </tr>
                <tr>
                    <th class="code">1</th>
                    <th class="description">2</th>
                    <th class="money">3</th>
                    <th class="money">4</th>
                    <th class="money">5</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section"><td class="code">4.</td><td class="description">PENDAPATAN</td><td class="money"></td><td class="money"></td><td class="money"></td></tr>
                <tr class="type"><td class="code">4.2.</td><td class="description indent-1">Pendapatan Transfer</td><td class="money">{{ $format($pendapatanAnggaran) }}</td><td class="money">{{ $format($pendapatanRealisasi) }}</td><td class="money">{{ $format($pendapatanLebih) }}</td></tr>
                @forelse($pendapatanRows as $row)
                    <tr class="{{ $row['class'] }}">
                        <td class="code">{{ $kode($row['code']) }}</td>
                        <td class="description indent-2">{{ $row['description'] }}</td>
                        <td class="money">{{ $format($row['anggaran']) }}</td>
                        <td class="money">{{ $format($row['realisasi']) }}</td>
                        <td class="money">{{ $format($row['anggaran'] - $row['realisasi']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="code">{{ $kode($kodeSumber === \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA ? '4.2.2' : '4.2.1') }}</td>
                        <td class="description indent-2">{{ $kodeSumber === \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA ? 'Alokasi Dana Desa' : 'Dana Desa' }}</td>
                        <td class="money">0,00</td><td class="money">0,00</td><td class="money">0,00</td>
                    </tr>
                @endforelse
                <tr class="total"><td class="code"></td><td class="description">JUMLAH PENDAPATAN</td><td class="money">{{ $format($pendapatanAnggaran) }}</td><td class="money">{{ $format($pendapatanRealisasi) }}</td><td class="money">{{ $format($pendapatanLebih) }}</td></tr>

                <tr class="section"><td class="code">5.</td><td class="description">BELANJA</td><td class="money"></td><td class="money"></td><td class="money"></td></tr>
                @foreach($ringkasan['belanjaRows'] as $row)
                    <tr class="{{ $row['class'] }}">
                        <td class="code">{{ $kode($row['code']) }}</td>
                        <td class="description indent-{{ min($row['level'], 4) }}">{{ $row['description'] }}</td>
                        <td class="money">{{ $format($row['anggaran']) }}</td>
                        <td class="money">{{ $format($row['realisasi']) }}</td>
                        <td class="money">{{ $format($row['anggaran'] - $row['realisasi']) }}</td>
                    </tr>
                @endforeach
                <tr class="total"><td class="code"></td><td class="description">JUMLAH BELANJA</td><td class="money">{{ $format($ringkasan['belanjaAnggaran']) }}</td><td class="money">{{ $format($ringkasan['belanjaRealisasi']) }}</td><td class="money">{{ $format($ringkasan['belanjaAnggaran'] - $ringkasan['belanjaRealisasi']) }}</td></tr>
                <tr class="total"><td class="code"></td><td class="description">SURPLUS/(DEFISIT)</td><td class="money">{{ $format($ringkasan['surplusAnggaran']) }}</td><td class="money">{{ $format($ringkasan['surplusRealisasi']) }}</td><td class="money">{{ $format($ringkasan['surplusAnggaran'] - $ringkasan['surplusRealisasi']) }}</td></tr>

                <tr class="section"><td class="code">6.</td><td class="description">PEMBIAYAAN</td><td class="money"></td><td class="money"></td><td class="money"></td></tr>
                <tr class="type"><td class="code">6.1.</td><td class="description indent-1">Penerimaan Pembiayaan</td><td class="money">{{ $format($ringkasan['penerimaanAnggaran']) }}</td><td class="money">{{ $format($ringkasan['penerimaanRealisasi']) }}</td><td class="money">{{ $format($ringkasan['penerimaanAnggaran'] - $ringkasan['penerimaanRealisasi']) }}</td></tr>
                @foreach($ringkasan['pembiayaanPenerimaan'] as $item)
                    <tr><td class="code">{{ $kode($item->rekening?->kode) }}</td><td class="description indent-2">{{ $item->uraian }}</td><td class="money">{{ $format($item->anggaran) }}</td><td class="money">{{ $format($item->realisasi) }}</td><td class="money">{{ $format($item->anggaran - $item->realisasi) }}</td></tr>
                @endforeach
                <tr class="type"><td class="code">6.2.</td><td class="description indent-1">Pengeluaran Pembiayaan</td><td class="money">{{ $format($ringkasan['pengeluaranAnggaran']) }}</td><td class="money">{{ $format($ringkasan['pengeluaranRealisasi']) }}</td><td class="money">{{ $format($ringkasan['pengeluaranAnggaran'] - $ringkasan['pengeluaranRealisasi']) }}</td></tr>
                @foreach($ringkasan['pembiayaanPengeluaran'] as $item)
                    <tr><td class="code">{{ $kode($item->rekening?->kode) }}</td><td class="description indent-2">{{ $item->uraian }}</td><td class="money">{{ $format($item->anggaran) }}</td><td class="money">{{ $format($item->realisasi) }}</td><td class="money">{{ $format($item->anggaran - $item->realisasi) }}</td></tr>
                @endforeach
                <tr class="total"><td class="code"></td><td class="description">PEMBIAYAAN NETTO</td><td class="money">{{ $format($ringkasan['nettoAnggaran']) }}</td><td class="money">{{ $format($ringkasan['nettoRealisasi']) }}</td><td class="money">{{ $format($ringkasan['nettoAnggaran'] - $ringkasan['nettoRealisasi']) }}</td></tr>
                <tr class="total"><td class="code"></td><td class="description">SISA LEBIH/(KURANG) PERHITUNGAN ANGGARAN (SiLPA)</td><td class="money">{{ $format($ringkasan['silpaAnggaran']) }}</td><td class="money">{{ $format($ringkasan['silpaRealisasi']) }}</td><td class="money">{{ $format($ringkasan['silpaAnggaran'] - $ringkasan['silpaRealisasi']) }}</td></tr>
            </tbody>
        </table>
        <div class="signature">
            <p>Waindawula, {{ \Carbon\Carbon::parse($sampaiTanggal)->translatedFormat('d F Y') }}</p>
            <p><strong>KEPALA DESA</strong></p>
            <div class="space"></div>
            <p><strong>................................</strong></p>
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('Arial', 'normal');
            $size = 7.5;
            $black = [0, 0, 0];
            $left = 38;
            $bottom = $pdf->get_height() - 26;

            $pdf->page_text($left, $bottom, 'Printed by Sistem PANDUWA', $font, $size, $black);
            $pdf->page_text($left, $bottom + 10, '{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}', $font, $size, $black);

            $pageText = 'Halaman {PAGE_NUM}';
            $pageWidth = $fontMetrics->get_text_width($pageText, $font, $size);
            $pdf->page_text($pdf->get_width() - $pageWidth - 38, $bottom, $pageText, $font, $size, $black);
        }
    </script>
</body>
</html>

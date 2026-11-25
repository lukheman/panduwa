<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Pelaksanaan APBDes per Sumberdana</title>
    <style>
        @page { size: A4 portrait; margin: 122px 16px 58px 16px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 9px; }

        table.report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report thead { display: table-header-group; }
        .report tr { page-break-inside: avoid; }
        .report th, .report td { border: 0.6px solid #000; padding: 2px 4px; vertical-align: middle; }
        .report th:last-child, .report td:last-child { border-right: 0.6px solid #000; }
        .report thead th { height: 25px; text-align: center; font-weight: bold; }
        .report thead th small { display: block; font-size: 8.5px; font-weight: bold; }
        .report td { height: 15px; }
        .report td.kode { width: 12%; padding-left: 6px; white-space: nowrap; }
        .report td.uraian { width: 43%; }
        .report td.money { width: 15%; text-align: right; white-space: nowrap; padding-right: 6px; }

        .filter th { padding: 4px 2px; font-weight: bold; }
        .filter table { width: 100%; border-collapse: collapse; }
        .filter table td { border: 0; padding: 0 2px; font-size: 9px; font-weight: bold; }
        .filter table td:last-child { text-align: right; }

        .baris-akun td { font-weight: bold; }
        .baris-kelompok td { font-weight: bold; }
        .baris-jenis td { font-weight: normal; }
        .baris-bidang td { font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .baris-sub_bidang td { font-weight: bold; text-decoration: underline; }
        .baris-kegiatan td { font-weight: bold; }
        .baris-kelompok_belanja td { font-weight: bold; }
        .baris-jenis_belanja td { font-weight: bold; font-style: italic; }
        .baris-obyek td { font-weight: normal; }
        .baris-total td { font-weight: bold; }
        .baris-total td.uraian { text-align: center; }

        .tanda-tangan { width: 250px; margin: 22px 0 0 auto; text-align: center; page-break-inside: avoid; }
        .tanda-tangan p { margin: 0; line-height: 1.5; }
        .tanda-tangan .jabatan { font-weight: bold; }
        .tanda-tangan .ruang { height: 72px; position: relative; }
        .tanda-tangan .ruang img { position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%); max-height: 70px; }
        .tanda-tangan .ruang .stempel { max-height: 62px; left: 22%; }
        .tanda-tangan .ruang .ttd { max-height: 52px; left: 76%; }
        .tanda-tangan .nama { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    @php
        $formatAngka = function ($nilai) {
            if ($nilai === null) {
                return '';
            }

            $nilai = round((float) $nilai, 2);
            if (abs($nilai) < 0.005) {
                $nilai = 0.0;
            }

            $teks = number_format(abs($nilai), 2, ',', '.');

            return $nilai < 0 ? '('.$teks.')' : $teks;
        };

        $kodeSumber = $sumberDana?->kode ?? \App\Models\SumberDana::KODE_DANA_DESA;
        $namaSumber = $sumberDana?->nama ?? 'Dana Desa';
        $labelSumber = trim($kodeSumber.' '.$namaSumber);

        if ($kodeSumber === \App\Models\SumberDana::KODE_DANA_DESA && ! str_contains(strtolower($labelSumber), 'dropping')) {
            $labelSumber .= ' (Dropping APBN)';
        } elseif ($kodeSumber === \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA && ! str_contains(strtolower($labelSumber), 'apbd')) {
            $labelSumber .= ' (APBD)';
        }

        $namaDesa = $namaDesa ?? config('desa.nama', 'WAINDAWULA');
        $namaKepalaDesa = $namaKepalaDesa ?? '-';
        $tanggalTandaTangan = $tanggalTandaTangan ?? $tanggalCetak ?? now()->translatedFormat('d F Y');
        $dicetakPada = $dicetakPada ?? now()->format('d/m/Y H:i:s');
        $footerLabel = $footerLabel ?? 'Printed by PANDUWA';
    @endphp

    <table class="report">
        <thead>
            <tr class="filter">
                <th colspan="5">
                    <table>
                        <tr>
                            <td>Sumberdana&nbsp;:&nbsp;{{ $labelSumber }}</td>
                            <td>Realisasi s.d {{ \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th>KODE REK</th>
                <th>URAIAN</th>
                <th>ANGGARAN<small>(Rp)</small></th>
                <th>REALISASI<small>(Rp)</small></th>
                <th>LEBIH/(KURANG)<small>(Rp)</small></th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ringkasan['rows'] as $baris)
                <tr class="baris-{{ $baris['gaya'] }}">
                    <td class="kode">{{ $baris['kode'] }}</td>
                    <td class="uraian" style="padding-left: {{ 5 + $baris['indent'] * 10 }}px;">{{ $baris['uraian'] }}</td>
                    <td class="money">{{ $formatAngka($baris['anggaran']) }}</td>
                    <td class="money">{{ $formatAngka($baris['realisasi']) }}</td>
                    <td class="money">{{ $formatAngka($baris['lebih']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="tanda-tangan">
        <p>{{ $namaDesa }}, {{ $tanggalTandaTangan }}</p>
        <p class="jabatan">KEPALA DESA</p>
        <div class="ruang">
            @if (! empty($stempelPath))
                <img class="stempel" src="{{ $stempelPath }}" alt="Stempel">
            @endif
            @if (! empty($ttdPath))
                <img class="ttd" src="{{ $ttdPath }}" alt="Tanda tangan">
            @endif
        </div>
        <p class="nama">{{ strtoupper((string) $namaKepalaDesa) }}</p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $judulDesa = @json('PEMERINTAH DESA '.strtoupper((string) $namaDesa));
            $judulTahun = @json('TAHUN ANGGARAN '.$tahun);
            $teksFooter = @json($footerLabel);
            $teksCetak = @json('Tanggal Cetak : '.$dicetakPada);

            $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($judulDesa, $judulTahun, $teksFooter, $teksCetak) {
                $font = $fontMetrics->getFont('Helvetica', 'normal');
                $fontTebal = $fontMetrics->getFont('Helvetica', 'bold') ?: $font;
                $hitam = [0, 0, 0];
                $putih = [1, 1, 1];
                $tinggiHalaman = $canvas->get_height();
                $lebarHalaman = $canvas->get_width();

                $tulisTengah = function ($teks, $y, $ukuran, $jenisFont) use ($canvas, $fontMetrics, $hitam) {
                    $lebarTeks = $fontMetrics->get_text_width($teks, $jenisFont, $ukuran);
                    $canvas->text(($canvas->get_width() - $lebarTeks) / 2, $y, $teks, $jenisFont, $ukuran, $hitam);
                };

                if ((int) $pageNumber === 1) {
                    $tulisTengah('LAPORAN REALISASI PELAKSANAAN', 18, 12, $fontTebal);
                    $tulisTengah('ANGGARAN PENDAPATAN DAN BELANJA DESA PER SUMBERDANA', 34, 10.5, $fontTebal);
                    $tulisTengah($judulDesa, 50, 10.5, $fontTebal);
                    $tulisTengah($judulTahun, 66, 10.5, $fontTebal);
                } else {
                    $tulisTengah($judulDesa, 30, 10.5, $fontTebal);
                    $tulisTengah($judulTahun, 46, 10.5, $fontTebal);
                }

                $canvas->filled_rectangle(16, $tinggiHalaman - 40, 94, 13, $hitam);
                $canvas->text(20, $tinggiHalaman - 36, $teksFooter, $font, 7.5, $putih);
                $canvas->text(16, $tinggiHalaman - 20, $teksCetak, $font, 7.5, $hitam);

                $teksHalaman = 'Halaman '.(int) $pageNumber;
                $canvas->text(
                    $lebarHalaman - $fontMetrics->get_text_width($teksHalaman, $font, 7.5) - 16,
                    $tinggiHalaman - 20,
                    $teksHalaman,
                    $font,
                    7.5,
                    $hitam
                );
            });
        }
    </script>
</body>
</html>

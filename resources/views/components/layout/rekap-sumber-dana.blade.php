@props(['ringkasan' => []])

@php
    $format = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    $gaya = [
        \App\Models\SumberDana::KODE_DANA_DESA => ['primary', 'fas fa-wallet'],
        \App\Models\SumberDana::KODE_ALOKASI_DANA_DESA => ['success', 'fas fa-coins'],
    ];
@endphp

<div class="row g-4 mb-4">
    @foreach($ringkasan as $item)
        @php
            [$variant, $icon] = $gaya[$item['kode']] ?? ['secondary', 'fas fa-money-bill'];
        @endphp

        <div class="col-lg-6">
            <x-layout.modern-card class="h-100">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-{{ $variant }} bg-opacity-10 text-{{ $variant }}">
                            <i class="{{ $icon }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-0 text-body">{{ $item['nama'] }}</h5>
                            <small class="text-muted">{{ $item['kode'] }}</small>
                        </div>
                    </div>
                    <x-ui.badge variant="{{ $variant }}">{{ $item['kode'] }}</x-ui.badge>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Pemasukan</small>
                            <span class="fw-bold text-success">{{ $format($item['pemasukan']) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Pengeluaran</small>
                            <span class="fw-bold text-danger">{{ $format($item['pengeluaran']) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Rencana Anggaran</small>
                            <span class="fw-bold text-body">{{ $format($item['anggaran']) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Sisa Saldo</small>
                            <span class="fw-bold {{ $item['saldo'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ $format($item['saldo']) }}</span>
                        </div>
                    </div>
                </div>
            </x-layout.modern-card>
        </div>
    @endforeach
</div>

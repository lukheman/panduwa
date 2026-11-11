<?php

namespace App\Livewire\Admin;

use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SumberDana;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Pemasukan')]
class PemasukanManagement extends Component
{
    use WithPagination;

    private const REKENING_PENDAPATAN = [
        'PAD' => '4.1.1',
        'DDS' => '4.2.1',
        'ADD' => '4.2.2',
        'DBH' => '4.2.3',
        'LAINNYA' => '4.3.1',
    ];

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'dana')]
    public string $dana = SumberDana::KODE_DANA_DESA;

    public ?int $sumber_dana_id = null;

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    public ?int $editingPemasukanId = null;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingPemasukanId = null;

    public ?Pemasukan $viewingPemasukan = null;

    public bool $showViewModal = false;

    protected function rules(): array
    {
        return [
            'sumber_dana_id' => ['required', 'integer', 'exists:sumber_dana,id'],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
        $this->sumber_dana_id = $this->sumberDanaId();
        $this->tanggal = date('Y-m-d');
    }

    public function updatedDana(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
        $this->tanggal = date('Y-m-d');
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingPemasukanId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $pemasukan = Pemasukan::findOrFail($id);

        $this->editingPemasukanId = $id;
        $this->sumber_dana_id = $pemasukan->sumber_dana_id
            ?? SumberDana::where('nama', $pemasukan->sumber_dana)->value('id')
            ?? $this->sumberDanaId();
        $this->jumlah = (string) $pemasukan->jumlah;
        $this->tanggal = $pemasukan->tanggal;
        $this->keterangan = $pemasukan->keterangan ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->sumber_dana_id = $this->sumberDanaId();
        $validated = $this->validate();

        $sumberDana = SumberDana::findOrFail($validated['sumber_dana_id']);
        $validated['sumber_dana'] = $sumberDana->nama;
        $validated['rekening_anggaran_id'] = $this->rekeningPendapatanId($sumberDana->kode);
        $validated['anggaran'] = $validated['jumlah'];

        if ($this->editingPemasukanId) {
            $pemasukan = Pemasukan::findOrFail($this->editingPemasukanId);
            $pemasukan->update($validated);
            session()->flash('success', 'Data pemasukan berhasil diperbarui.');
        } else {
            Pemasukan::create($validated);
            session()->flash('success', 'Data pemasukan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function openViewModal(int $id): void
    {
        $this->viewingPemasukan = Pemasukan::findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingPemasukan = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingPemasukanId = $id;
        $this->showDeleteModal = true;
    }

    public function deletePemasukan(): void
    {
        if ($this->deletingPemasukanId) {
            Pemasukan::destroy($this->deletingPemasukanId);
            session()->flash('success', 'Data pemasukan berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingPemasukanId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPemasukanId = null;
    }

    protected function resetForm(): void
    {
        $this->sumber_dana_id = $this->sumberDanaId();
        $this->jumlah = '';
        $this->tanggal = date('Y-m-d');
        $this->keterangan = '';
        $this->editingPemasukanId = null;
    }

    protected function sumberDanaId(): ?int
    {
        return SumberDana::query()->where('kode', $this->dana)->value('id');
    }

    protected function rekeningPendapatanId(string $kode): ?int
    {
        $kodeRekening = self::REKENING_PENDAPATAN[$kode] ?? null;

        return $kodeRekening
            ? RekeningAnggaran::where('kode', $kodeRekening)->value('id')
            : null;
    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function render()
    {
        $sumberDana = SumberDana::query()->where('kode', $this->dana)->first();
        $sumberDanaId = $sumberDana?->id;

        $query = Pemasukan::query()->untukSumberDana($sumberDanaId);

        $totalPemasukan = (clone $query)->sum('jumlah');
        $totalTahunIni = (clone $query)->whereYear('tanggal', date('Y'))->sum('jumlah');
        $totalPengeluaran = Pengeluaran::query()->untukSumberDana($sumberDanaId)->sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $rekapSumberDana = collect([
            [
                'kode' => $sumberDana?->kode ?? $this->dana,
                'nama' => $sumberDana?->nama ?? SumberDana::labelUntukKode($this->dana),
                'tahun_ini' => $totalTahunIni,
                'total' => $totalPemasukan,
            ],
        ]);

        $pemasukans = $query->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('sumber_dana', 'like', '%'.$this->search.'%')
                    ->orWhereHas('sumberDana', fn ($sumber) => $sumber->where('nama', 'like', '%'.$this->search.'%'))
                    ->orWhere('keterangan', 'like', '%'.$this->search.'%');
            });
        })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.pemasukan-management', [
            'pemasukans' => $pemasukans,
            'sumberDana' => $sumberDana,
            'danaLabel' => SumberDana::labelUntukKode($this->dana),
            'rekapSumberDana' => $rekapSumberDana,
            'totalPemasukan' => $totalPemasukan,
            'totalTahunIni' => $totalTahunIni,
            'sisaAnggaran' => $sisaAnggaran,
        ]);
    }
}

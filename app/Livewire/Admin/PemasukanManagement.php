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

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
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
            ?? SumberDana::where('kode', 'DDS')->value('id');
        $this->jumlah = (string) $pemasukan->jumlah;
        $this->tanggal = $pemasukan->tanggal;
        $this->keterangan = $pemasukan->keterangan ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
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
        $this->sumber_dana_id = null;
        $this->jumlah = '';
        $this->tanggal = date('Y-m-d');
        $this->keterangan = '';
        $this->editingPemasukanId = null;
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
        $query = Pemasukan::query();

        $totalPemasukan = (clone $query)->sum('jumlah');
        $totalTahunIni = (clone $query)->whereYear('tanggal', date('Y'))->sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $sumberDanas = SumberDana::query()
            ->where('aktif', true)
            ->orderBy('id')
            ->get();

        $rekapSumberDana = $sumberDanas->map(fn (SumberDana $sumber) => [
            'kode' => $sumber->kode,
            'nama' => $sumber->nama,
            'tahun_ini' => (clone $query)->where('sumber_dana_id', $sumber->id)
                ->whereYear('tanggal', date('Y'))->sum('jumlah'),
            'total' => (clone $query)->where('sumber_dana_id', $sumber->id)->sum('jumlah'),
        ]);

        $totalTanpaSumber = (clone $query)->whereNull('sumber_dana_id')->sum('jumlah');

        if ($totalTanpaSumber > 0) {
            $rekapSumberDana->push([
                'kode' => '-',
                'nama' => 'Tanpa Sumber Dana',
                'tahun_ini' => (clone $query)->whereNull('sumber_dana_id')
                    ->whereYear('tanggal', date('Y'))->sum('jumlah'),
                'total' => $totalTanpaSumber,
            ]);
        }

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
            'sumberDanas' => $sumberDanas,
            'rekapSumberDana' => $rekapSumberDana,
            'totalPemasukan' => $totalPemasukan,
            'totalTahunIni' => $totalTahunIni,
            'sisaAnggaran' => $sisaAnggaran,
        ]);
    }
}

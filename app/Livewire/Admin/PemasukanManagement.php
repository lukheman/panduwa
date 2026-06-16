<?php

namespace App\Livewire\Admin;

use App\Models\Pemasukan;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Pemasukan')]
class PemasukanManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $sumber_dana = '';
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
            'sumber_dana' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'numeric', 'min:0'],
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
        $this->sumber_dana = $pemasukan->sumber_dana;
        $this->jumlah = (string) $pemasukan->jumlah;
        $this->tanggal = $pemasukan->tanggal;
        $this->keterangan = $pemasukan->keterangan ?? '';
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

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
        $this->sumber_dana = '';
        $this->jumlah = '';
        $this->tanggal = date('Y-m-d');
        $this->keterangan = '';
        $this->editingPemasukanId = null;
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function render()
    {
        $query = Pemasukan::query();
        
        $totalPemasukan = (clone $query)->sum('jumlah');
        $totalBulanIni = (clone $query)->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'))->sum('jumlah');
        $totalPengeluaran = \App\Models\Pengeluaran::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $pemasukans = $query->when($this->search, function ($q) {
                $q->where('sumber_dana', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%');
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.pemasukan-management', [
            'pemasukans' => $pemasukans,
            'totalPemasukan' => $totalPemasukan,
            'totalBulanIni' => $totalBulanIni,
            'sisaAnggaran' => $sisaAnggaran,
        ]);
    }
}

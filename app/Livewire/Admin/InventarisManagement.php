<?php

namespace App\Livewire\Admin;

use App\Models\Inventaris;
use App\Models\Pengeluaran;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Inventaris')]
class InventarisManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $kode_barang = '';
    public string $nama_barang = '';
    public string $lokasi = '';
    public string $tanggal_perolehan = '';
    public string $nilai_aset = '';
    public string $kondisi = 'Baik';

    public ?int $editingInventarisId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingInventarisId = null;

    public ?Inventaris $viewingInventaris = null;
    public bool $showViewModal = false;

    protected function rules(): array
    {
        $rules = [
            'nama_barang' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'tanggal_perolehan' => ['required', 'date'],
            'nilai_aset' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'kondisi' => ['required', 'string', 'max:255'],
        ];

        if ($this->editingInventarisId) {
            $rules['kode_barang'] = ['required', 'string', 'max:255', 'unique:inventaris,kode_barang,' . $this->editingInventarisId];
        } else {
            $rules['kode_barang'] = ['required', 'string', 'max:255', 'unique:inventaris,kode_barang'];
        }

        return $rules;
    }

    public function mount()
    {
        $this->tanggal_perolehan = date('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        // Generate automatic Kode Barang template based on timestamp or something similar
        $this->kode_barang = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $this->editingInventarisId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $inventaris = Inventaris::findOrFail($id);
        
        $this->editingInventarisId = $id;
        $this->kode_barang = $inventaris->kode_barang;
        $this->nama_barang = $inventaris->nama_barang;
        $this->lokasi = $inventaris->lokasi;
        $this->tanggal_perolehan = $inventaris->tanggal_perolehan;
        $this->nilai_aset = (string) $inventaris->nilai_aset;
        $this->kondisi = $inventaris->kondisi;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingInventarisId) {
            $inventaris = Inventaris::findOrFail($this->editingInventarisId);
            $inventaris->update($validated);
            session()->flash('success', 'Data inventaris berhasil diperbarui.');
        } else {
            Inventaris::create($validated);
            session()->flash('success', 'Data inventaris berhasil ditambahkan.');
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
        $this->viewingInventaris = Inventaris::with('pengeluaran')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingInventaris = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingInventarisId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteInventaris(): void
    {
        if ($this->deletingInventarisId) {
            Inventaris::destroy($this->deletingInventarisId);
            session()->flash('success', 'Data inventaris berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingInventarisId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingInventarisId = null;
    }

    protected function resetForm(): void
    {
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->lokasi = '';
        $this->tanggal_perolehan = date('Y-m-d');
        $this->nilai_aset = '';
        $this->kondisi = 'Baik';
        $this->editingInventarisId = null;
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
    
    public function getKondisiBadgeVariant($kondisi)
    {
        $kondisi = strtolower($kondisi);
        if (str_contains($kondisi, 'baik')) return 'success';
        if (str_contains($kondisi, 'ringan')) return 'warning';
        if (str_contains($kondisi, 'berat') || str_contains($kondisi, 'rusak')) return 'danger';
        return 'secondary';
    }

    public function render()
    {
        $query = Inventaris::query()->with(['pengeluaran']);

        $totalAset = (clone $query)->count();
        $totalNilaiAset = (clone $query)->sum('nilai_aset');
        $asetKondisiBaik = (clone $query)->where('kondisi', 'Baik')->count();

        $inventarises = $query->when($this->search, function ($q) {
                $q->where('nama_barang', 'like', '%' . $this->search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $this->search . '%')
                    ->orWhere('lokasi', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.inventaris-management', [
            'inventarises' => $inventarises,
            'totalAset' => $totalAset,
            'totalNilaiAset' => $totalNilaiAset,
            'asetKondisiBaik' => $asetKondisiBaik,
        ]);
    }
}

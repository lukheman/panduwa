<?php

namespace App\Livewire\Admin;

use App\Models\MutasiAset;
use App\Models\Inventaris;
use App\Models\Bendahara;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Mutasi Aset')]
class MutasiAsetManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $id_inventaris = null;
    public string $jenis_mutasi = 'Dijual';
    public string $tanggal = '';
    public string $keterangan = '';
    public ?int $id_bendahara = null;

    public ?int $editingMutasiId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingMutasiId = null;

    protected function rules(): array
    {
        return [
            'id_inventaris' => ['required', 'exists:inventaris,id'],
            'jenis_mutasi' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'id_bendahara' => ['required', 'exists:bendahara,id'],
        ];
    }

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
        
        $bendahara = Bendahara::first();
        if ($bendahara) {
            $this->id_bendahara = $bendahara->id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingMutasiId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $mutasi = MutasiAset::findOrFail($id);
        
        $this->editingMutasiId = $id;
        $this->id_inventaris = $mutasi->id_inventaris;
        $this->jenis_mutasi = $mutasi->jenis_mutasi;
        $this->tanggal = $mutasi->tanggal;
        $this->keterangan = $mutasi->keterangan ?? '';
        $this->id_bendahara = $mutasi->id_bendahara;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingMutasiId) {
            $mutasi = MutasiAset::findOrFail($this->editingMutasiId);
            $mutasi->update($validated);
            session()->flash('success', 'Data mutasi aset berhasil diperbarui.');
        } else {
            MutasiAset::create($validated);
            
            // Optionally update the condition of the inventory item
            $inventaris = Inventaris::find($this->id_inventaris);
            if ($inventaris) {
                // Assuming mutasi usually means it's gone or broken
                if (in_array(strtolower($this->jenis_mutasi), ['dijual', 'dihibahkan', 'dipindah tangankan'])) {
                    $inventaris->kondisi = 'Dipindahtangankan';
                } elseif (strtolower($this->jenis_mutasi) === 'dimusnahkan') {
                    $inventaris->kondisi = 'Rusak Berat / Dimusnahkan';
                }
                $inventaris->save();
            }

            session()->flash('success', 'Data mutasi aset berhasil dicatat.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingMutasiId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteMutasi(): void
    {
        if ($this->deletingMutasiId) {
            MutasiAset::destroy($this->deletingMutasiId);
            session()->flash('success', 'Catatan mutasi aset berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingMutasiId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingMutasiId = null;
    }

    protected function resetForm(): void
    {
        $this->id_inventaris = null;
        $this->jenis_mutasi = 'Dijual';
        $this->tanggal = date('Y-m-d');
        $this->keterangan = '';
        $this->editingMutasiId = null;
        
        $bendahara = Bendahara::first();
        if ($bendahara) {
            $this->id_bendahara = $bendahara->id;
        }
    }

    public function getMutasiBadgeVariant($jenis)
    {
        $jenis = strtolower($jenis);
        if (str_contains($jenis, 'jual')) return 'success';
        if (str_contains($jenis, 'hibah')) return 'info';
        if (str_contains($jenis, 'musnah') || str_contains($jenis, 'rusak')) return 'danger';
        if (str_contains($jenis, 'pindah')) return 'warning';
        return 'secondary';
    }

    public function render()
    {
        $mutasis = MutasiAset::query()
            ->with(['inventaris', 'bendahara'])
            ->when($this->search, function ($query) {
                $query->where('keterangan', 'like', '%' . $this->search . '%')
                    ->orWhere('jenis_mutasi', 'like', '%' . $this->search . '%')
                    ->orWhereHas('inventaris', function ($q) {
                        $q->where('nama_barang', 'like', '%' . $this->search . '%')
                          ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $inventarises = Inventaris::orderBy('nama_barang')->get();
        $bendaharas = Bendahara::all();

        return view('livewire.admin.mutasi-aset-management', [
            'mutasis' => $mutasis,
            'inventarises' => $inventarises,
            'bendaharas' => $bendaharas,
        ]);
    }
}

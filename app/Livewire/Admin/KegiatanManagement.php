<?php

namespace App\Livewire\Admin;

use App\Models\Kegiatan;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Title('Manajemen Kegiatan')]
class KegiatanManagement extends Component
{
    use WithPagination;
    use WithFileUploads;

    #[Url(as: 'q')]
    public string $search = '';

    public string $nama_kegiatan = '';
    public string $lokasi = '';
    public string $anggaran = '';
    public string $status = 'perencanaan';
    public $foto_progres;
    public ?string $existing_foto_progres = null;

    public ?int $editingKegiatanId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showDetailModal = false;
    public ?int $deletingKegiatanId = null;
    public ?int $detailKegiatanId = null;
    public $detailKegiatan = null;

    protected function rules(): array
    {
        return [
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'anggaran' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\StatusKegiatan::class)],
            'foto_progres' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingKegiatanId = null;
        $this->showModal = true;
    }

    public function openDetailModal(int $id): void
    {
        $this->detailKegiatanId = $id;
        $this->detailKegiatan = Kegiatan::with('pengeluarans.kategori')
            ->withSum('pengeluarans', 'jumlah')
            ->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailKegiatanId = null;
        $this->detailKegiatan = null;
    }

    public function openEditModal(int $id): void
    {
        $kegiatan = Kegiatan::findOrFail($id);
        
        $this->editingKegiatanId = $id;
        $this->nama_kegiatan = $kegiatan->nama_kegiatan;
        $this->lokasi = $kegiatan->lokasi;
        $this->anggaran = (string) $kegiatan->anggaran;
        $this->status = $kegiatan->status->value;
        $this->existing_foto_progres = $kegiatan->foto_progres;
        $this->foto_progres = null;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Cek sisa anggaran yang bisa dialokasikan
        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $totalPengeluaranNonKegiatan = \App\Models\Pengeluaran::whereNull('id_kegiatan')->sum('jumlah');
        $totalAnggaranKegiatanLain = \App\Models\Kegiatan::query();
        
        if ($this->editingKegiatanId) {
            $totalAnggaranKegiatanLain->where('id', '!=', $this->editingKegiatanId);
        }
        
        $totalAnggaranKegiatanLain = $totalAnggaranKegiatanLain->sum('anggaran');
        
        $sisaAnggaranTersedia = $totalPemasukan - $totalPengeluaranNonKegiatan - $totalAnggaranKegiatanLain;

        $allowedToSave = false;

        if ($this->editingKegiatanId) {
            $oldAnggaran = \App\Models\Kegiatan::where('id', $this->editingKegiatanId)->value('anggaran');
            if ($this->anggaran <= $oldAnggaran) {
                $allowedToSave = true;
            }
        }

        if (!$allowedToSave && $this->anggaran > $sisaAnggaranTersedia) {
            $this->addError('anggaran', 'Sisa anggaran desa yang belum dialokasikan hanya ' . $this->formatRupiah($sisaAnggaranTersedia) . '. Anda tidak dapat mengeset anggaran melebihi batas ini.');
            return;
        }

        $path = $this->existing_foto_progres;
        
        if ($this->foto_progres) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            $path = $this->foto_progres->store('kegiatan', 'public');
        }

        $data = [
            'nama_kegiatan' => $this->nama_kegiatan,
            'lokasi' => $this->lokasi,
            'anggaran' => $this->anggaran,
            'status' => $this->status,
            'foto_progres' => $path,
        ];

        if ($this->editingKegiatanId) {
            $kegiatan = Kegiatan::findOrFail($this->editingKegiatanId);
            $kegiatan->update($data);
            session()->flash('success', 'Data kegiatan berhasil diperbarui.');
        } else {
            Kegiatan::create($data);
            session()->flash('success', 'Data kegiatan berhasil ditambahkan.');
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
        $this->deletingKegiatanId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteKegiatan(): void
    {
        if ($this->deletingKegiatanId) {
            $kegiatan = Kegiatan::find($this->deletingKegiatanId);
            if ($kegiatan && $kegiatan->foto_progres) {
                Storage::disk('public')->delete($kegiatan->foto_progres);
            }
            if ($kegiatan) {
                $kegiatan->delete();
                session()->flash('success', 'Data kegiatan berhasil dihapus.');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingKegiatanId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingKegiatanId = null;
    }

    protected function resetForm(): void
    {
        $this->nama_kegiatan = '';
        $this->lokasi = '';
        $this->anggaran = '';
        $this->status = 'perencanaan';
        $this->foto_progres = null;
        $this->existing_foto_progres = null;
        $this->editingKegiatanId = null;
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function getStatusBadgeVariant($status)
    {
        if ($status instanceof \App\Enums\StatusKegiatan) {
            return $status->getColor();
        }
        return 'warning';
    }
    
    public function getStatusIcon($status)
    {
        if ($status instanceof \App\Enums\StatusKegiatan) {
            return $status->getIcon();
        }
        return 'fas fa-calendar-alt';
    }

    public function render()
    {
        $kegiatans = Kegiatan::query()
            ->withSum('pengeluarans', 'jumlah')
            ->when($this->search, function ($query) {
                $query->where('nama_kegiatan', 'like', '%' . $this->search . '%')
                    ->orWhere('lokasi', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.kegiatan-management', [
            'kegiatans' => $kegiatans,
        ]);
    }
}

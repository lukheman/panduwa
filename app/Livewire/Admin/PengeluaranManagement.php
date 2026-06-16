<?php

namespace App\Livewire\Admin;

use App\Models\Pengeluaran;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Pengeluaran')]
class PengeluaranManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $tanggal = '';
    public string $jumlah = '';
    public string $keterangan = '';
    public ?int $id_kategori_transaksi = null;
    public ?int $id_kegiatan = null;
    
    public ?array $selectedKegiatanInfo = null;

    public ?int $editingPengeluaranId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingPengeluaranId = null;

    protected function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'id_kategori_transaksi' => ['required', 'exists:kategori_transaksi,id'],
            'id_kegiatan' => ['nullable', 'exists:kegiatan,id'],
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

    public function updatedIdKegiatan($value): void
    {
        if ($value) {
            $kegiatan = Kegiatan::withSum('pengeluarans', 'jumlah')->find($value);
            if ($kegiatan) {
                $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                
                // Jika sedang mengedit, kurangi jumlah pengeluaran ini agar sisa anggaran akurat
                if ($this->editingPengeluaranId) {
                    $currentPengeluaran = Pengeluaran::find($this->editingPengeluaranId);
                    if ($currentPengeluaran && $currentPengeluaran->id_kegiatan == $value) {
                        $realisasi -= $currentPengeluaran->jumlah;
                    }
                }
                
                $sisa = $kegiatan->anggaran - $realisasi;
                $this->selectedKegiatanInfo = [
                    'anggaran' => $kegiatan->anggaran,
                    'realisasi' => $realisasi,
                    'sisa' => $sisa,
                ];
            } else {
                $this->selectedKegiatanInfo = null;
            }
        } else {
            $this->selectedKegiatanInfo = null;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingPengeluaranId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        $this->editingPengeluaranId = $id;
        $this->tanggal = $pengeluaran->tanggal;
        $this->jumlah = (string) $pengeluaran->jumlah;
        $this->keterangan = $pengeluaran->keterangan ?? '';
        $this->id_kategori_transaksi = $pengeluaran->id_kategori_transaksi;
        $this->id_kegiatan = $pengeluaran->id_kegiatan;
        
        $this->updatedIdKegiatan($this->id_kegiatan);
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert empty string for id_kegiatan to null
        if (empty($validated['id_kegiatan'])) {
            $validated['id_kegiatan'] = null;
        }

        if ($this->editingPengeluaranId) {
            $pengeluaran = Pengeluaran::findOrFail($this->editingPengeluaranId);
            $pengeluaran->update($validated);
            session()->flash('success', 'Data pengeluaran berhasil diperbarui.');
        } else {
            Pengeluaran::create($validated);
            session()->flash('success', 'Data pengeluaran berhasil ditambahkan.');
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
        $this->deletingPengeluaranId = $id;
        $this->showDeleteModal = true;
    }

    public function deletePengeluaran(): void
    {
        if ($this->deletingPengeluaranId) {
            Pengeluaran::destroy($this->deletingPengeluaranId);
            session()->flash('success', 'Data pengeluaran berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingPengeluaranId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPengeluaranId = null;
    }

    protected function resetForm(): void
    {
        $this->tanggal = date('Y-m-d');
        $this->jumlah = '';
        $this->keterangan = '';
        $this->id_kategori_transaksi = null;
        $this->id_kegiatan = null;
        $this->selectedKegiatanInfo = null;
        $this->editingPengeluaranId = null;
    }

    public function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    public function render()
    {
        $query = Pengeluaran::query()->with(['kategori', 'kegiatan']);

        $totalPengeluaran = (clone $query)->sum('jumlah');
        $totalBulanIni = (clone $query)->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'))->sum('jumlah');

        $pengeluarans = $query->when($this->search, function ($q) {
                $q->where('keterangan', 'like', '%' . $this->search . '%')
                    ->orWhereHas('kategori', function ($q2) {
                        $q2->where('nama_kategori', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $kategoris = KategoriTransaksi::all();
        $kegiatans = Kegiatan::where('status', '!=', 'selesai')->get();

        return view('livewire.admin.pengeluaran-management', [
            'pengeluarans' => $pengeluarans,
            'kategoris' => $kategoris,
            'kegiatans' => $kegiatans,
            'totalPengeluaran' => $totalPengeluaran,
            'totalBulanIni' => $totalBulanIni,
        ]);
    }
}

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

    // Inventaris integration
    public bool $is_inventaris = false;
    public string $inventaris_kode = '';
    public string $inventaris_nama = '';
    public string $inventaris_lokasi = '';
    public string $inventaris_kondisi = 'baik';
    
    public ?array $selectedKegiatanInfo = null;

    public ?int $editingPengeluaranId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingPengeluaranId = null;

    public ?Pengeluaran $viewingPengeluaran = null;
    public bool $showViewModal = false;

    protected function rules(): array
    {
        $rules = [
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'keterangan' => ['nullable', 'string'],
            'id_kategori_transaksi' => ['required', 'exists:kategori_transaksi,id'],
            'id_kegiatan' => ['nullable', 'exists:kegiatan,id'],
        ];

        if ($this->is_inventaris) {
            $rules['inventaris_kode'] = ['required', 'string', 'max:50'];
            $rules['inventaris_nama'] = ['required', 'string', 'max:255'];
            $rules['inventaris_lokasi'] = ['required', 'string', 'max:255'];
            $rules['inventaris_kondisi'] = ['required', 'in:baik,rusak ringan,rusak berat'];
        }

        return $rules;
    }

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedIsInventaris($value): void
    {
        if ($value && empty($this->inventaris_kode)) {
            $this->inventaris_kode = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        }
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
        $pengeluaran = Pengeluaran::with('inventaris')->findOrFail($id);
        
        $this->editingPengeluaranId = $id;
        $this->tanggal = $pengeluaran->tanggal;
        $this->jumlah = (string) $pengeluaran->jumlah;
        $this->keterangan = $pengeluaran->keterangan ?? '';
        $this->id_kategori_transaksi = $pengeluaran->id_kategori_transaksi;
        $this->id_kegiatan = $pengeluaran->id_kegiatan;
        
        if ($pengeluaran->inventaris) {
            $this->is_inventaris = true;
            $this->inventaris_kode = $pengeluaran->inventaris->kode_barang;
            $this->inventaris_nama = $pengeluaran->inventaris->nama_barang;
            $this->inventaris_lokasi = $pengeluaran->inventaris->lokasi;
            $this->inventaris_kondisi = $pengeluaran->inventaris->kondisi;
        } else {
            $this->is_inventaris = false;
            $this->inventaris_kode = '';
            $this->inventaris_nama = '';
            $this->inventaris_lokasi = '';
            $this->inventaris_kondisi = 'baik';
        }
        
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

        // Check overall sisa anggaran
        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');
        
        if ($this->editingPengeluaranId) {
            $current = Pengeluaran::find($this->editingPengeluaranId);
            if ($current) {
                $totalPengeluaran -= $current->jumlah;
            }
        }
        
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;
        
        if ($validated['jumlah'] > $sisaAnggaran) {
            $this->addError('jumlah', 'Sisa Anggaran Keseluruhan Desa (' . $this->formatRupiah($sisaAnggaran) . ') tidak mencukupi untuk pengeluaran ini.');
            return;
        }

        // Check if kegiatan budget is enough
        if ($validated['id_kegiatan']) {
            $kegiatan = Kegiatan::withSum('pengeluarans', 'jumlah')->find($validated['id_kegiatan']);
            if ($kegiatan) {
                $realisasi = $kegiatan->pengeluarans_sum_jumlah ?? 0;
                
                if ($this->editingPengeluaranId && isset($current) && $current->id_kegiatan == $validated['id_kegiatan']) {
                    $realisasi -= $current->jumlah;
                }
                
                $sisaKegiatan = $kegiatan->anggaran - $realisasi;
                
                if ($validated['jumlah'] > $sisaKegiatan) {
                    $this->addError('jumlah', 'Sisa Anggaran untuk Kegiatan ini (' . $this->formatRupiah($sisaKegiatan) . ') tidak mencukupi.');
                    return;
                }
            }
        }

        if ($this->editingPengeluaranId) {
            $pengeluaran = Pengeluaran::findOrFail($this->editingPengeluaranId);
            $pengeluaran->update([
                'tanggal' => $validated['tanggal'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'id_kategori_transaksi' => $validated['id_kategori_transaksi'],
                'id_kegiatan' => $validated['id_kegiatan'],
            ]);

            if ($this->is_inventaris) {
                \App\Models\Inventaris::updateOrCreate(
                    ['id_pengeluaran' => $pengeluaran->id],
                    [
                        'kode_barang' => $this->inventaris_kode,
                        'nama_barang' => $this->inventaris_nama,
                        'lokasi' => $this->inventaris_lokasi,
                        'kondisi' => $this->inventaris_kondisi,
                        'tanggal_perolehan' => $this->tanggal,
                        'nilai_aset' => $this->jumlah,
                    ]
                );
            } else {
                \App\Models\Inventaris::where('id_pengeluaran', $pengeluaran->id)->delete();
            }

            session()->flash('success', 'Data pengeluaran berhasil diperbarui.');
        } else {
            $pengeluaran = Pengeluaran::create([
                'tanggal' => $validated['tanggal'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'id_kategori_transaksi' => $validated['id_kategori_transaksi'],
                'id_kegiatan' => $validated['id_kegiatan'],
            ]);

            if ($this->is_inventaris) {
                \App\Models\Inventaris::create([
                    'id_pengeluaran' => $pengeluaran->id,
                    'kode_barang' => $this->inventaris_kode,
                    'nama_barang' => $this->inventaris_nama,
                    'lokasi' => $this->inventaris_lokasi,
                    'kondisi' => $this->inventaris_kondisi,
                    'tanggal_perolehan' => $this->tanggal,
                    'nilai_aset' => $this->jumlah,
                ]);
            }

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

    public function openViewModal(int $id): void
    {
        $this->viewingPengeluaran = Pengeluaran::with(['kategori', 'kegiatan', 'inventaris'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingPengeluaran = null;
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
        
        $this->is_inventaris = false;
        $this->inventaris_kode = '';
        $this->inventaris_nama = '';
        $this->inventaris_lokasi = '';
        $this->inventaris_kondisi = 'baik';
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
        $totalPemasukan = \App\Models\Pemasukan::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

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
        $kegiatans = Kegiatan::where('status', '!=', \App\Enums\StatusKegiatan::SELESAI)->get();

        return view('livewire.admin.pengeluaran-management', [
            'pengeluarans' => $pengeluarans,
            'kategoris' => $kategoris,
            'kegiatans' => $kegiatans,
            'totalPengeluaran' => $totalPengeluaran,
            'totalBulanIni' => $totalBulanIni,
            'sisaAnggaran' => $sisaAnggaran,
        ]);
    }
}

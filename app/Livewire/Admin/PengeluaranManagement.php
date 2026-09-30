<?php

namespace App\Livewire\Admin;

use App\Enums\KondisiInventaris;
use App\Enums\StatusKegiatan;
use App\Models\Inventaris;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Pengeluaran')]
class PengeluaranManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $tanggal = '';

    public string $jumlah = '';

    public string $keterangan = '';

    public ?int $id_kegiatan = null;

    public bool $catatSebagaiInventaris = false;

    public ?int $id_inventaris = null;

    public string $kode_barang = '';

    public string $nama_barang = '';

    public string $kondisi = KondisiInventaris::BAIK->value;

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

            'id_kegiatan' => ['nullable', 'exists:kegiatan,id'],
            'catatSebagaiInventaris' => ['boolean'],
            'kode_barang' => [
                Rule::requiredIf($this->catatSebagaiInventaris),
                'nullable',
                'string',
                'max:255',
                Rule::unique('inventaris', 'kode_barang')->ignore($this->id_inventaris),
            ],
            'nama_barang' => [Rule::requiredIf($this->catatSebagaiInventaris), 'nullable', 'string', 'max:255'],
            'kondisi' => [Rule::requiredIf($this->catatSebagaiInventaris), Rule::enum(KondisiInventaris::class)],
        ];

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

                $sisa = $kegiatan->rencana_anggaran - $realisasi;
                $this->selectedKegiatanInfo = [
                    'anggaran' => $kegiatan->rencana_anggaran,
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

    public function updatedCatatSebagaiInventaris(bool $value): void
    {
        if ($value && $this->kode_barang === '') {
            $this->kode_barang = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
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

        $this->id_kegiatan = $pengeluaran->id_kegiatan;
        $inventaris = $pengeluaran->inventaris;
        $this->catatSebagaiInventaris = $inventaris !== null;
        $this->id_inventaris = $inventaris?->id;
        $this->kode_barang = $inventaris?->kode_barang ?? '';
        $this->nama_barang = $inventaris?->nama_barang ?? '';
        $this->kondisi = $inventaris?->kondisi?->value ?? KondisiInventaris::BAIK->value;
        $this->updatedIdKegiatan($this->id_kegiatan);

        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['id_kegiatan'] = $validated['id_kegiatan'] ?: null;
        // Check overall sisa anggaran
        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaran = Pengeluaran::sum('jumlah');

        if ($this->editingPengeluaranId) {
            $current = Pengeluaran::find($this->editingPengeluaranId);
            if ($current) {
                $totalPengeluaran -= $current->jumlah;
            }
        }

        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        if ($validated['jumlah'] > $sisaAnggaran) {
            $this->addError('jumlah', 'Sisa Anggaran Keseluruhan Desa ('.$this->formatRupiah($sisaAnggaran).') tidak mencukupi untuk pengeluaran ini.');

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

                $sisaKegiatan = $kegiatan->rencana_anggaran - $realisasi;

                if ($validated['jumlah'] > $sisaKegiatan) {
                    $this->addError('jumlah', 'Sisa Anggaran untuk Kegiatan ini ('.$this->formatRupiah($sisaKegiatan).') tidak mencukupi.');

                    return;
                }
            }
        }

        DB::transaction(function () use ($validated): void {
            $pengeluaran = $this->editingPengeluaranId
                ? Pengeluaran::findOrFail($this->editingPengeluaranId)
                : new Pengeluaran;

            $previousInventaris = $pengeluaran->inventaris;

            $pengeluaran->fill([
                'tanggal' => $validated['tanggal'],
                'jumlah' => $validated['jumlah'],
                'keterangan' => $validated['keterangan'],
                'id_kegiatan' => $validated['id_kegiatan'],
            ]);
            $pengeluaran->save();

            if ($validated['catatSebagaiInventaris']) {
                $inventaris = $previousInventaris ?? new Inventaris;
                $inventaris->fill([
                    'kode_barang' => $validated['kode_barang'],
                    'nama_barang' => $validated['nama_barang'],
                    'tanggal_perolehan' => $validated['tanggal'],
                    'nilai_aset' => $validated['jumlah'],
                    'kondisi' => $validated['kondisi'],
                    'id_pengeluaran' => $pengeluaran->id,
                ]);
                $inventaris->save();
            } elseif ($previousInventaris) {
                $previousInventaris->update(['id_pengeluaran' => null]);
            }
        });

        session()->flash(
            'success',
            $this->editingPengeluaranId
                ? 'Data pengeluaran berhasil diperbarui.'
                : 'Data pengeluaran berhasil ditambahkan.'
        );

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
        $this->viewingPengeluaran = Pengeluaran::with(['kegiatan', 'inventaris'])->findOrFail($id);
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

        $this->id_kegiatan = null;
        $this->catatSebagaiInventaris = false;
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->kondisi = KondisiInventaris::BAIK->value;
        $this->selectedKegiatanInfo = null;
        $this->editingPengeluaranId = null;

    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function render()
    {
        $query = Pengeluaran::query()->with(['kegiatan', 'inventaris']);

        $totalPengeluaran = (clone $query)->sum('jumlah');
        $totalBulanIni = (clone $query)->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'))->sum('jumlah');
        $totalPemasukan = Pemasukan::sum('jumlah');
        $sisaAnggaran = $totalPemasukan - $totalPengeluaran;

        $pengeluarans = $query->when($this->search, function ($q) {
            $q->where('keterangan', 'like', '%'.$this->search.'%');
        })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $kegiatans = Kegiatan::where('status', '!=', StatusKegiatan::PERENCANAAN)->get();

        return view('livewire.admin.pengeluaran-management', [
            'pengeluarans' => $pengeluarans,

            'kegiatans' => $kegiatans,
            'kondisiInventaris' => KondisiInventaris::cases(),
            'totalPengeluaran' => $totalPengeluaran,
            'totalBulanIni' => $totalBulanIni,
            'sisaAnggaran' => $sisaAnggaran,
        ]);
    }
}

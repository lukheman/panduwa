<?php

namespace App\Livewire\Admin;

use App\Enums\KondisiInventaris;
use App\Models\Inventaris;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Inventaris')]
class InventarisManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $kode_barang = '';

    public string $nama_barang = '';

    public string $jumlah = '1';

    public string $tanggal_perolehan = '';

    public string $nilai_aset = '';

    public ?int $sumber_dana_id = null;

    public ?int $id_pengeluaran = null;

    public string $keterangan = '';

    public string $kondisi = KondisiInventaris::BAIK->value;

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
            'jumlah' => ['required', 'integer', 'min:1', 'max:100000'],
            'tanggal_perolehan' => ['required', 'date'],
            'nilai_aset' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'sumber_dana_id' => ['required', 'integer', 'exists:sumber_dana,id'],
            'id_pengeluaran' => ['nullable', 'integer', 'exists:pengeluaran,id'],
            'keterangan' => ['nullable', 'string'],
            'kondisi' => ['required', Rule::enum(KondisiInventaris::class)],
        ];

        if ($this->editingInventarisId) {
            $rules['kode_barang'] = ['required', 'string', 'max:255', 'unique:inventaris,kode_barang,'.$this->editingInventarisId];
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

    public function updatedIdPengeluaran($value): void
    {
        if (! $value) {
            return;
        }

        $pengeluaran = Pengeluaran::with('sumberDana')->find($value);

        if (! $pengeluaran) {
            return;
        }

        $this->sumber_dana_id = $pengeluaran->sumber_dana_id ?? $this->sumberDanaDanaDesaId();
        $this->nilai_aset = (string) $pengeluaran->jumlah;
        $this->tanggal_perolehan = $pengeluaran->tanggal;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        // Generate automatic Kode Barang template based on timestamp or something similar
        $this->kode_barang = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
        $this->sumber_dana_id = $this->sumberDanaDanaDesaId();
        $this->editingInventarisId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $inventaris = Inventaris::findOrFail($id);

        $this->editingInventarisId = $id;
        $this->kode_barang = $inventaris->kode_barang;
        $this->nama_barang = $inventaris->nama_barang;
        $this->jumlah = (string) $inventaris->jumlah;
        $this->tanggal_perolehan = $inventaris->tanggal_perolehan;
        $this->nilai_aset = (string) $inventaris->nilai_aset;
        $this->sumber_dana_id = $inventaris->sumber_dana_id;
        $this->id_pengeluaran = $inventaris->id_pengeluaran;
        $this->keterangan = $inventaris->keterangan ?? '';
        $this->kondisi = $inventaris->kondisi->value;

        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($validated['id_pengeluaran'] ?? null) {
            $pengeluaran = Pengeluaran::findOrFail($validated['id_pengeluaran']);
            $validated['sumber_dana_id'] = $pengeluaran->sumber_dana_id ?? $this->sumberDanaDanaDesaId();
            $validated['nilai_aset'] = $pengeluaran->jumlah;
            $validated['tanggal_perolehan'] = $pengeluaran->tanggal;
        }

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
        $this->viewingInventaris = Inventaris::with(['pengeluaran.sumberDana', 'sumberDana'])->findOrFail($id);
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
        $this->jumlah = '1';
        $this->tanggal_perolehan = date('Y-m-d');
        $this->nilai_aset = '';
        $this->sumber_dana_id = null;
        $this->id_pengeluaran = null;
        $this->keterangan = '';
        $this->kondisi = KondisiInventaris::BAIK->value;
        $this->editingInventarisId = null;
    }

    protected function sumberDanaDanaDesaId(): ?int
    {
        return SumberDana::query()->where('kode', SumberDana::KODE_DANA_DESA)->value('id');
    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function getKondisiBadgeVariant($kondisi)
    {
        if ($kondisi instanceof KondisiInventaris) {
            return $kondisi->getColor();
        }

        return KondisiInventaris::tryFrom(strtolower((string) $kondisi))?->getColor() ?? 'secondary';
    }

    public function render()
    {
        $query = Inventaris::query()->with(['pengeluaran.sumberDana', 'sumberDana']);

        $totalAset = (clone $query)->sum('jumlah');
        $totalNilaiAset = (clone $query)->sum('nilai_aset');
        $asetKondisiBaik = (clone $query)->where('kondisi', KondisiInventaris::BAIK)->sum('jumlah');

        $inventarises = $query->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('nama_barang', 'like', '%'.$this->search.'%')
                    ->orWhere('kode_barang', 'like', '%'.$this->search.'%')
                    ->orWhere('keterangan', 'like', '%'.$this->search.'%');
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $sumberDanas = SumberDana::query()->where('aktif', true)->orderBy('id')->get();

        $pengeluarans = Pengeluaran::query()
            ->with('sumberDana')
            ->where(function ($q) {
                $q->whereDoesntHave('inventaris');

                if ($this->id_pengeluaran) {
                    $q->orWhere('id', $this->id_pengeluaran);
                }
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('livewire.admin.inventaris-management', [
            'inventarises' => $inventarises,
            'sumberDanas' => $sumberDanas,
            'pengeluarans' => $pengeluarans,
            'totalAset' => $totalAset,
            'totalNilaiAset' => $totalNilaiAset,
            'asetKondisiBaik' => $asetKondisiBaik,
        ]);
    }
}

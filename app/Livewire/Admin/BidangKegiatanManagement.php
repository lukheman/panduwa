<?php

namespace App\Livewire\Admin;

use App\Models\BidangKegiatan;
use App\Models\SubBidangKegiatan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Bidang & Sub-Bidang')]
class BidangKegiatanManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public array $expandedBidang = [];

    // Bidang form
    public string $bidang_kode = '';

    public string $bidang_nama = '';

    public string $bidang_deskripsi = '';

    public ?int $editingBidangId = null;

    public bool $showBidangModal = false;

    public bool $showDeleteBidangModal = false;

    public ?int $deletingBidangId = null;

    // Sub-bidang form
    public string $sub_bidang_kegiatan_id = '';

    public string $sub_kode = '';

    public string $sub_nama = '';

    public string $sub_deskripsi = '';

    public ?int $editingSubId = null;

    public bool $showSubModal = false;

    public bool $showDeleteSubModal = false;

    public ?int $deletingSubId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $bidangId): void
    {
        if (in_array($bidangId, $this->expandedBidang)) {
            $this->expandedBidang = array_values(array_diff($this->expandedBidang, [$bidangId]));
        } else {
            $this->expandedBidang[] = $bidangId;
        }
    }

    // ===== Bidang CRUD =====

    public function openCreateBidangModal(): void
    {
        $this->resetBidangForm();
        $this->editingBidangId = null;
        $this->showBidangModal = true;
    }

    public function openEditBidangModal(int $id): void
    {
        $bidang = BidangKegiatan::findOrFail($id);

        $this->editingBidangId = $id;
        $this->bidang_kode = $bidang->kode;
        $this->bidang_nama = $bidang->nama;
        $this->bidang_deskripsi = $bidang->deskripsi ?? '';
        $this->showBidangModal = true;
    }

    public function saveBidang(): void
    {
        $this->validate([
            'bidang_kode' => ['required', 'string', 'max:20', Rule::unique('bidang_kegiatan', 'kode')->ignore($this->editingBidangId)],
            'bidang_nama' => ['required', 'string', 'max:255'],
            'bidang_deskripsi' => ['nullable', 'string'],
        ]);

        $data = [
            'kode' => $this->bidang_kode,
            'nama' => $this->bidang_nama,
            'deskripsi' => $this->bidang_deskripsi ?: null,
        ];

        if ($this->editingBidangId) {
            BidangKegiatan::findOrFail($this->editingBidangId)->update($data);
            session()->flash('success', 'Bidang kegiatan berhasil diperbarui.');
        } else {
            BidangKegiatan::create($data);
            session()->flash('success', 'Bidang kegiatan berhasil ditambahkan.');
        }

        $this->closeBidangModal();
    }

    public function closeBidangModal(): void
    {
        $this->showBidangModal = false;
        $this->resetBidangForm();
        $this->resetValidation();
    }

    public function confirmDeleteBidang(int $id): void
    {
        $this->deletingBidangId = $id;
        $this->showDeleteBidangModal = true;
    }

    public function deleteBidang(): void
    {
        if ($this->deletingBidangId) {
            $bidang = BidangKegiatan::withCount('kegiatans')->find($this->deletingBidangId);
            if ($bidang) {
                if ($bidang->kegiatans_count > 0) {
                    session()->flash('error', 'Bidang tidak dapat dihapus karena masih memiliki '.$bidang->kegiatans_count.' kegiatan terkait.');
                } else {
                    $bidang->delete();
                    session()->flash('success', 'Bidang beserta sub-bidangnya berhasil dihapus.');
                }
            }
        }

        $this->showDeleteBidangModal = false;
        $this->deletingBidangId = null;
    }

    public function cancelDeleteBidang(): void
    {
        $this->showDeleteBidangModal = false;
        $this->deletingBidangId = null;
    }

    protected function resetBidangForm(): void
    {
        $this->bidang_kode = '';
        $this->bidang_nama = '';
        $this->bidang_deskripsi = '';
        $this->editingBidangId = null;
    }

    // ===== Sub-bidang CRUD =====

    public function openCreateSubModal(?int $bidangId = null): void
    {
        $this->resetSubForm();
        $this->editingSubId = null;
        if ($bidangId) {
            $this->sub_bidang_kegiatan_id = (string) $bidangId;
        }
        $this->showSubModal = true;
    }

    public function openEditSubModal(int $id): void
    {
        $sub = SubBidangKegiatan::findOrFail($id);

        $this->editingSubId = $id;
        $this->sub_bidang_kegiatan_id = (string) $sub->bidang_kegiatan_id;
        $this->sub_kode = $sub->kode;
        $this->sub_nama = $sub->nama;
        $this->sub_deskripsi = $sub->deskripsi ?? '';
        $this->showSubModal = true;
    }

    public function saveSub(): void
    {
        $this->validate([
            'sub_bidang_kegiatan_id' => ['required', 'exists:bidang_kegiatan,id'],
            'sub_kode' => ['required', 'string', 'max:20'],
            'sub_nama' => ['required', 'string', 'max:255'],
            'sub_deskripsi' => ['nullable', 'string'],
        ]);

        $exists = SubBidangKegiatan::where('bidang_kegiatan_id', $this->sub_bidang_kegiatan_id)
            ->where('kode', $this->sub_kode)
            ->when($this->editingSubId, fn ($q) => $q->where('id', '!=', $this->editingSubId))
            ->exists();

        if ($exists) {
            $this->addError('sub_kode', 'Kode sub-bidang sudah digunakan pada bidang ini.');

            return;
        }

        $data = [
            'bidang_kegiatan_id' => $this->sub_bidang_kegiatan_id,
            'kode' => $this->sub_kode,
            'nama' => $this->sub_nama,
            'deskripsi' => $this->sub_deskripsi ?: null,
        ];

        if ($this->editingSubId) {
            SubBidangKegiatan::findOrFail($this->editingSubId)->update($data);
            session()->flash('success', 'Sub-bidang berhasil diperbarui.');
        } else {
            SubBidangKegiatan::create($data);
            session()->flash('success', 'Sub-bidang berhasil ditambahkan.');
        }

        $this->closeSubModal();
    }

    public function closeSubModal(): void
    {
        $this->showSubModal = false;
        $this->resetSubForm();
        $this->resetValidation();
    }

    public function confirmDeleteSub(int $id): void
    {
        $this->deletingSubId = $id;
        $this->showDeleteSubModal = true;
    }

    public function deleteSub(): void
    {
        if ($this->deletingSubId) {
            $sub = SubBidangKegiatan::withCount('kegiatans')->find($this->deletingSubId);
            if ($sub) {
                if ($sub->kegiatans_count > 0) {
                    session()->flash('error', 'Sub-bidang tidak dapat dihapus karena masih memiliki '.$sub->kegiatans_count.' kegiatan terkait.');
                } else {
                    $sub->delete();
                    session()->flash('success', 'Sub-bidang berhasil dihapus.');
                }
            }
        }

        $this->showDeleteSubModal = false;
        $this->deletingSubId = null;
    }

    public function cancelDeleteSub(): void
    {
        $this->showDeleteSubModal = false;
        $this->deletingSubId = null;
    }

    protected function resetSubForm(): void
    {
        $this->sub_bidang_kegiatan_id = '';
        $this->sub_kode = '';
        $this->sub_nama = '';
        $this->sub_deskripsi = '';
        $this->editingSubId = null;
    }

    public function render()
    {
        $bidangs = BidangKegiatan::query()
            ->with(['subBidangs.kegiatans', 'subBidangs' => function ($q) {
                if ($this->search) {
                    $q->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('kode', 'like', '%'.$this->search.'%');
                }
                $q->orderBy('kode');
            }])
            ->withCount(['subBidangs', 'kegiatans'])
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('kode', 'like', '%'.$this->search.'%')
                    ->orWhereHas('subBidangs', function ($q) {
                        $q->where('nama', 'like', '%'.$this->search.'%')
                            ->orWhere('kode', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('kode')
            ->paginate(10);

        $allBidangs = BidangKegiatan::orderBy('kode')->get();

        return view('livewire.admin.bidang-kegiatan-management', [
            'bidangs' => $bidangs,
            'allBidangs' => $allBidangs,
        ]);
    }
}

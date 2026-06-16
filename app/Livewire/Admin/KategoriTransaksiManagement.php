<?php

namespace App\Livewire\Admin;

use App\Models\KategoriTransaksi;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kategori Transaksi')]
class KategoriTransaksiManagement extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $nama_kategori = '';
    public string $deskripsi = '';

    public ?int $editingKategoriId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingKategoriId = null;

    protected function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingKategoriId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $kategori = KategoriTransaksi::findOrFail($id);
        
        $this->editingKategoriId = $id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->deskripsi = $kategori->deskripsi ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingKategoriId) {
            $kategori = KategoriTransaksi::findOrFail($this->editingKategoriId);
            $kategori->update($validated);
            session()->flash('success', 'Kategori Transaksi updated successfully.');
        } else {
            KategoriTransaksi::create($validated);
            session()->flash('success', 'Kategori Transaksi created successfully.');
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
        $this->deletingKategoriId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteKategori(): void
    {
        if ($this->deletingKategoriId) {
            KategoriTransaksi::destroy($this->deletingKategoriId);
            session()->flash('success', 'Kategori Transaksi deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->deletingKategoriId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingKategoriId = null;
    }

    protected function resetForm(): void
    {
        $this->nama_kategori = '';
        $this->deskripsi = '';
        $this->editingKategoriId = null;
    }

    public function render()
    {
        $kategoris = KategoriTransaksi::query()
            ->when($this->search, function ($query) {
                $query->where('nama_kategori', 'like', '%' . $this->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.kategori-transaksi-management', [
            'kategoris' => $kategoris,
        ]);
    }
}

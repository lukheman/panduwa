<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\Bendahara;
use App\Models\KepalaDesa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Pengguna')]
class UserManagement extends Component
{
    use WithPagination;

    // Search & Filter
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'role')]
    public string $roleType = 'admin'; // 'admin', 'bendahara', 'kepala_desa'

    // Form fields
    public string $nama = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // State
    public ?int $editingUserId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;

    protected function rules(): array
    {
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];

        $table = $this->getTableName();

        if ($this->editingUserId) {
            $pk = $this->roleType === 'admin' ? 'id_admin' : 'id';
            $rules['email'][] = "unique:{$table},email," . $this->editingUserId . ",{$pk}";
            if ($this->password) {
                $rules['password'] = ['confirmed', Password::defaults()];
            }
        } else {
            $rules['email'][] = "unique:{$table},email";
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleType(): void
    {
        $this->resetPage();
    }

    protected function getModelClass()
    {
        return match($this->roleType) {
            'bendahara' => Bendahara::class,
            'kepala_desa' => KepalaDesa::class,
            default => Admin::class,
        };
    }

    protected function getTableName()
    {
        return match($this->roleType) {
            'bendahara' => 'bendahara',
            'kepala_desa' => 'kepala_desa',
            default => 'admin',
        };
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingUserId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $modelClass = $this->getModelClass();
        $user = $modelClass::findOrFail($userId);

        $this->editingUserId = $userId;
        $this->nama = $user->nama;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $modelClass = $this->getModelClass();

        if ($this->editingUserId) {
            $user = $modelClass::findOrFail($this->editingUserId);
            $user->nama = $validated['nama'];
            $user->email = $validated['email'];

            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }

            $user->save();
            $roleName = $this->roleType === 'kepala_desa' ? 'Kepala Desa' : ucfirst($this->roleType);
            session()->flash('success', $roleName . ' berhasil diperbarui.');
        } else {
            $modelClass::create([
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $roleName = $this->roleType === 'kepala_desa' ? 'Kepala Desa' : ucfirst($this->roleType);
            session()->flash('success', $roleName . ' berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function confirmDelete(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->deletingUserId) {
            $modelClass = $this->getModelClass();
            $modelClass::destroy($this->deletingUserId);
            $roleName = $this->roleType === 'kepala_desa' ? 'Kepala Desa' : ucfirst($this->roleType);
            session()->flash('success', $roleName . ' berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    protected function resetForm(): void
    {
        $this->nama = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->editingUserId = null;
    }

    public function render()
    {
        $modelClass = $this->getModelClass();
        $pk = $this->roleType === 'admin' ? 'id_admin' : 'id';

        $users = $modelClass::query()
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'pk' => $pk,
        ]);
    }
}

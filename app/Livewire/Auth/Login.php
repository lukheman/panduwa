<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Login - AdminPro')]
class Login extends Component
{
    #[Rule(['required', 'email'])]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public string $role = 'admin';

    public bool $remember = false;

    public function submit()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard($this->role)->attempt($credentials, $this->remember)) {
            session()->regenerate();
            $rolePrefix = $this->role === 'kepala_desa' ? 'kepala_desa' : $this->role;
            return redirect()->route($rolePrefix . '.dashboard');
        }

        $this->addError('email', __('auth.failed'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layoutData(['type' => 'auth']);
    }
}

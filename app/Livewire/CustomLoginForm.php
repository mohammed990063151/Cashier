<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomLoginForm extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public function authenticate()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();
        return redirect()->intended(route('dashboard.welcome'));
    }

    public function render()
    {
        return view('livewire.forms.custom-login-form');
    }
}

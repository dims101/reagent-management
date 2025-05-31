<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class Login extends Component
{
    public $nup;
    public $password;
    public $remember = false;

    protected $rules = [
        'nup' => 'required|string',
        'password' => 'required|string|min:8',
    ];

    public function login()
    {
        $this->validate();

        $key = 'login-attempts:' . Str::lower($this->nup) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('nup', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        // Find user by nup
        $user = User::where('nup', $this->nup)->first();

        if (!$user) {
            RateLimiter::hit($key, 30);
            $this->addError('nup', 'User with this NUP does not exist.');
            return;
        }

        if ($user->trashed()) {
            RateLimiter::hit($key, 30);
            $this->addError('nup', 'This account has been deactivated.');
            return;
        }

        if (!Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 30);
            $this->addError('password', 'The password is incorrect.');
            return;
        }

        $credentials = [
            'nup' => $this->nup,
            'password' => $this->password,
        ];

        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($key, 30);
            $this->addError('nup', 'Authentication failed. Please contact support.');
            return;
        }

        RateLimiter::clear($key);
        session()->forget('error');

        return redirect()->intended('dashboard')->with('success', 'Login successful! Welcome back.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->extends('layouts.app')
            ->section('content');
    }
}

<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $email;
    public $password;
    public $remember;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $key = 'login-attempts:' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
            return null;
        }

        if (!Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($key, 30);
            $this->addError('email', __('auth.failed'));
            return null;
        }

        RateLimiter::clear($key);
        return redirect()->intended('dashboard')->with('success', 'Login successful! Welcome back.');
    }
    public function render()
    {
        return view('livewire.auth.login')->extends('layouts.app')->section('content');
    }
}

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
    public $email;
    public $password;
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ];

    public function login()
    {
        $this->validate();

        $key = 'login-attempts:' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        // Debug: Check if user exists
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            RateLimiter::hit($key, 30);
            $this->addError('email', 'User with this email does not exist.');
            return;
        }

        // Debug: Check if user is soft deleted
        if ($user->trashed()) {
            RateLimiter::hit($key, 30);
            $this->addError('email', 'This account has been deactivated.');
            return;
        }

        // Debug: Verify password manually
        if (!Hash::check($this->password, $user->password)) {
            RateLimiter::hit($key, 30);
            $this->addError('password', 'The password is incorrect.');
            return;
        }

        // Attempt authentication
        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (!Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($key, 30);
            $this->addError('email', 'Authentication failed. Please contact support.');
            return;
        }

        RateLimiter::clear($key);

        // Clear any session errors
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

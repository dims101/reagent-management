<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;

class ForgotPassword extends Component
{
    #[Validate('required|email|exists:users,email')]
    public $email;
    public function sendResetLink()
    {
        // $this->validate();

        // Logic to send the password reset link
        // This could involve using Laravel's built-in password reset functionality
        // or a custom implementation.

        // session()->flash('status', 'Password reset link sent to your email.');
    }
    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->extends('layouts.app')
            ->section('content');
    }
}

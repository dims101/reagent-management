<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Request;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Sidebar extends Component
{
    public $approvalCount = 0;

    // Change password modal properties
    public $showChangePasswordModal = false;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected $listeners = [
        'proceed-password-change' => 'changePassword',
        'approvalUpdated' => 'refreshApprovalCount'
    ];

    public function refreshApprovalCount()
    {
        $user = Auth::user();
        if ($user) {
            $this->approvalCount = Request::join('stocks', 'requests.reagent_id', '=', 'stocks.id')
                ->where('stocks.dept_owner_id', $user->dept_id)
                ->when($user->role_id == 2, function ($query) {
                    $query->where('status', 'waiting manager');
                })
                ->when($user->role_id == 3, function ($query) {
                    $query->whereIn('status', ['pending']);
                })
                ->count() ?? 0;
        } else {
            $this->approvalCount = 0;
        }
    }

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->approvalCount = Request::join('stocks', 'requests.reagent_id', '=', 'stocks.id')
                ->where('stocks.dept_owner_id', $user->dept_id)
                ->when($user->role_id == 2, function ($query) {
                    $query->where('status', 'waiting manager');
                })
                ->when($user->role_id == 3, function ($query) {
                    $query->whereIn('status', ['pending']);
                })
                ->count() ?? 0;
        } else {
            $this->approvalCount = 0;
        }
    }

    public function openChangePasswordModal()
    {
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->resetValidation();
        $this->showChangePasswordModal = true;
    }

    public function closeChangePasswordModal()
    {
        $this->showChangePasswordModal = false;
        $this->resetValidation();
    }

    public function showPasswordConfirmation()
    {
        // Validate the form first before showing confirmation
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required|min:8',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.confirmed' => 'New password and confirmation must match.',
            'new_password_confirmation.required' => 'Please confirm your new password.',
            'new_password_confirmation.min' => 'Confirmation password must be at least 8 characters.',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        // If validation passes, show confirmation dialog
        $this->dispatch('show-password-confirmation');
    }

    public function confirmPasswordChange()
    {
        // This method will be called after SweetAlert confirmation
        $this->changePassword();
    }

    public function changePassword()
    {
        // The validation is already done in showPasswordConfirmation method
        // So we can directly proceed with password change

        // Fix: Update the current authenticated user's password
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->new_password),
            'is_default_password' => false, // Set to false if you want to track default password usage
        ]);

        $this->closeChangePasswordModal();
        $this->dispatch('swal-success', [
            'icon' => 'success',
            'title' => 'Password Changed!',
            'text' => 'Your password has been updated successfully.'
        ]);
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}

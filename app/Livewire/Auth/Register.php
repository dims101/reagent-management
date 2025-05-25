<?php

namespace App\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Title('Register User')]
class Register extends Component
{
    public $subTitle = 'Register a new user';
    public $name;
    public $dept_id;
    public $nup;
    public $email;
    public $company;
    public $role_id;
    public $departments = [];
    public $roles = [];
    public $showEditModal = false;
    public $editUserId;
    public $edit_name, $edit_email, $edit_nup, $edit_dept_id, $edit_company, $edit_role_id;

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
        $this->roles = Role::orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'dept_id' => 'required|integer',
            'nup' => 'required|string|max:20|unique:users',
            'email' => 'required|email|max:50|unique:users',
            'company' => 'required|string|max:50',
            'role_id' => 'required|integer',
        ];
    }

    public function register()
    {
        $this->validate();

        try {
            $user = User::create([
                'name' => $this->name,
                'dept_id' => $this->dept_id,
                'nup' => $this->nup,
                'email' => $this->email,
                'company' => $this->company,
                'role_id' => $this->role_id,
                'is_default_password' => true,
                'password' => bcrypt($this->nup . '-lai-user'),
            ]);

            // Reset form fields
            $this->reset(['name', 'dept_id', 'nup', 'email', 'company', 'role_id']);
            $this->resetValidation();

            // Dispatch success event for SweetAlert
            $this->dispatch('userCreated', [
                'title' => 'Success!',
                'message' => 'User registered successfully.',
                'type' => 'success'
            ]);

            // Auto login (if this is for registration page)
            // Auth::login($user, true);
            // return redirect()->route('dashboard');

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Registration failed. Please try again.',
                'type' => 'error'
            ]);
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editUserId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_email = $user->email;
        $this->edit_nup = $user->nup;
        $this->edit_dept_id = $user->dept_id;
        $this->edit_company = $user->company;
        $this->edit_role_id = $user->role_id;
        $this->showEditModal = true;
    }

    public function confirmUpdateUser()
    {
        $this->validate([
            'edit_name' => 'required|string|max:100',
            'edit_email' => 'required|email|max:50|unique:users,email,' . $this->editUserId,
            'edit_nup' => 'required|string|max:20|unique:users,nup,' . $this->editUserId,
            'edit_dept_id' => 'required|integer',
            'edit_company' => 'required|string|max:50',
            'edit_role_id' => 'required|integer',
        ]);

        // Dispatch confirmation dialog
        $this->dispatch('confirmUpdate');
    }

    public function updateUser()
    {
        try {
            $user = User::findOrFail($this->editUserId);
            $user->update([
                'name' => $this->edit_name,
                'email' => $this->edit_email,
                'nup' => $this->edit_nup,
                'dept_id' => $this->edit_dept_id,
                'company' => $this->edit_company,
                'role_id' => $this->edit_role_id,
            ]);

            $this->closeModal();

            $this->dispatch('userUpdated', [
                'title' => 'Updated!',
                'message' => 'User updated successfully.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to update user. Please try again.',
                'type' => 'error'
            ]);
        }
    }
    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            $this->dispatch('userDeleted', [
                'title' => 'Deleted!',
                'message' => 'User deleted successfully.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to delete user. Please try again.',
                'type' => 'error'
            ]);
        }
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->reset(['editUserId', 'edit_name', 'edit_email', 'edit_nup', 'edit_dept_id', 'edit_company', 'edit_role_id']);
        $this->resetValidation();

        // Dispatch event to reinitialize DataTable
        $this->dispatch('modalClosed');
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'users' => User::all()
        ]);
    }
}

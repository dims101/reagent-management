<div class="sidebar sidebar-style-2">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    <img src="../assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle">
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            {{ auth()->user()->name ?? '-' }}
                            <span class="user-level">
                                {{ optional(auth()->user()->role)->name ?? '-' }}
                            </span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>

                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            @php
                                $isSuperUser = optional(auth()->user()->role)->name === 'Super User';
                            @endphp

                            @if ($isSuperUser)
                                <li class="nav-item {{ request()->routeIs('register') ? 'active' : '' }}">
                                    <a href="{{ route('register') }}" wire:navigate>
                                        <i class="fas fa-user-plus"></i>
                                        <p>User Registration</p>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a href="#" wire:click.prevent="openChangePasswordModal">
                                    <i class="fas fa-key"></i>
                                    <p>Change Password</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav nav-primary">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @php
                    $userRoleId = auth()->user()->role_id ?? null;
                @endphp
                @if ($userRoleId !== 4)
                    <li class="nav-item {{ request()->routeIs('create-stock') ? 'active' : '' }}">
                        <a href="{{ route('create-stock') }}" wire:navigate>
                            <i class="fas fa-plus-circle"></i>
                            <p>Input Stock</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item {{ request()->routeIs('self-stock') ? 'active' : '' }}">
                    <a href="{{ route('self-stock') }}" wire:navigate>
                        <i class="fas fa-box-open"></i>
                        <p>Stock On Hand</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('other-stock') ? 'active' : '' }}">
                    <a href="/stock/others" wire:navigate>
                        <i class="fas fa-boxes"></i>
                        <p>Stock Of Others</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('approval-list') ? 'active' : '' }}">
                    <a href="/approval" wire:navigate>
                        <i class="fas fa-clipboard-check"></i>
                        <p>
                            Approval List
                            @if ($approvalCount > 0)
                                <span class="badge badge-danger ml-2">{{ $approvalCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('reject') ? 'active' : '' }}">
                    <a href="/reject" wire:navigate>
                        <i class="fas fa-trash"></i>
                        <p>Reject List</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('history') ? 'active' : '' }}">
                    <a href="/history" wire:navigate>
                        <i class="fas fa-history"></i>
                        <p>History List</p>
                    </a>
                </li>
                @if (auth()->user()->dept_id == 1)
                    <li class="nav-item {{ request()->routeIs('assign-ticket') ? 'active' : '' }}">
                        <a href="/ticket/assign" wire:navigate>
                            <i class="fas fa-user-alt"></i>
                            <p>Assign Ticket Reagent</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item {{ request()->routeIs('create-ticket') ? 'active' : '' }}">
                    <a href="/ticket/create" wire:navigate>
                        <i class="fas fa-plus"></i>
                        <p>Create Ticket Reagent</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('show-ticket') ? 'active' : '' }}">
                    <a href="/ticket" wire:navigate>
                        <i class="fas fa-list-ul"></i>
                        <p>Ticket Reagent List</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    {{-- Change Password Modal --}}
    @if ($showChangePasswordModal)
        <div class="modal fade show d-flex align-items-center justify-content-center"
            style="display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:1050;" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form id="changePasswordForm" wire:submit.prevent="">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title text-dark">Change Password</h5>
                            <button type="button" class="close" wire:click="closeChangePasswordModal">
                                <span class="text-white">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Current Password</label>
                                <div class="input-group">
                                    <input type="password" id="current_password" class="form-control"
                                        wire:model.defer="current_password" required>
                                </div>
                                @error('current_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <div class="input-group">
                                    <input type="password" id="new_password" class="form-control"
                                        wire:model.defer="new_password" required>
                                </div>
                                @error('new_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" id="new_password_confirmation" class="form-control"
                                        wire:model.defer="new_password_confirmation" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox" style="margin-top: 10px;">
                                    <input type="checkbox" class="custom-control-input" id="showPassword">
                                    <label class="custom-control-label" for="showPassword">Show Passwords</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-pill"
                                wire:click="closeChangePasswordModal" wire:loading.attr="disabled"
                                wire:target="changePassword">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="button" id="confirmPasswordBtn" class="btn btn-primary btn-pill"
                                wire:loading.attr="disabled" wire:target="changePassword"
                                wire:click="showPasswordConfirmation">
                                <span wire:loading wire:target="changePassword"
                                    class="spinner-border spinner-border-sm mr-1"></span>
                                <i class="fas fa-check"></i> Confirm
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {

            // Show/Hide password functionality (works without Alpine.js)
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'showPassword') {
                    const currentPassword = document.getElementById('current_password');
                    const newPassword = document.getElementById('new_password');
                    const confirmPassword = document.getElementById('new_password_confirmation');

                    if (e.target.checked) {
                        currentPassword.type = 'text';
                        newPassword.type = 'text';
                        confirmPassword.type = 'text';
                    } else {
                        currentPassword.type = 'password';
                        newPassword.type = 'password';
                        confirmPassword.type = 'password';
                    }
                }
            });

            // Listen for confirmation event from Livewire
            Livewire.on('show-password-confirmation', () => {
                swal({
                    title: "Are you sure?",
                    text: "Do you want to change your password?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            className: "btn btn-secondary btn-pill"
                        },
                        confirm: {
                            text: "OK",
                            value: true,
                            visible: true,
                            className: "btn btn-primary btn-pill"
                        }
                    },
                    dangerMode: true,
                }).then((willChange) => {
                    if (willChange) {
                        // Dispatch event to proceed with password change
                        Livewire.dispatch('proceed-password-change');
                    }
                });
            });

            // Success SweetAlert
            Livewire.on('swal-success', (data) => {
                console.log('SweetAlert triggered:', data);

                const alertData = data[0];

                swal({
                    icon: alertData.icon,
                    title: alertData.title,
                    text: alertData.text,
                    buttons: false,
                    timer: 2000
                });
            });

        }, {
            once: true
        });
    </script>
@endpush

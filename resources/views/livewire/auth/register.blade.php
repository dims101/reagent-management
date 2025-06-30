{{-- filepath: resources/views/livewire/auth/register.blade.php --}}
<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-4">
        <div class="card full-height">
            <div class="card-body">
                <div class="card-title">Register</div>
                <form wire:submit.prevent="register">
                    @csrf

                    <div class="form-group">
                        <label for="nup">NUP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nup') is-invalid @enderror" id="nup"
                            wire:model.defer="nup" placeholder="Enter NUP" required>
                        @error('nup')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            wire:model.defer="name" placeholder="Enter your name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            wire:model.defer="email" placeholder="Enter your email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('role_id') is-invalid @enderror" id="role_id"
                            wire:model.defer="role_id" required>
                            <option value="">-- Select Role --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="dept_id">Department <span class="text-danger">*</span></label>
                        <select class="form-control @error('dept_id') is-invalid @enderror" id="dept_id"
                            wire:model.defer="dept_id" required>
                            <option value="">-- Select Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('dept_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company">Company <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('company') is-invalid @enderror" id="company"
                            wire:model.defer="company" placeholder="Enter company" required>
                        @error('company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-success btn-pill">
                            <i class="fa fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="card-title">User List</div>
                <div class="table-responsive mt-3">
                    <table id="user-datatable" class="display table table-striped table-hover datatable" wire:ignore>
                        <thead class="thead-light">
                            <tr>
                                <th>NUP</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->nup }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        {{ optional($departments->where('id', $user->dept_id)->first())->name ?? '-' }}
                                    </td>
                                    <td>
                                        <div class="form-button-action">
                                            <button type="button" class="btn btn-link btn-primary btn-lg"
                                                wire:click="edit({{ $user->id }})" data-toggle="tooltip"
                                                title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-link btn-warning"
                                                onclick="confirmReset({{ $user->id }})" data-toggle="tooltip"
                                                title="Reset Password">
                                                <i class="fa fa-key"></i>
                                            </button>
                                            <button type="button" class="btn btn-link btn-danger"
                                                onclick="confirmDelete({{ $user->id }})" data-toggle="tooltip"
                                                title="Delete">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit User --}}
    @if ($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="confirmUpdateUser">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit User</h5>
                            <button type="button" class="close" wire:click="closeModal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('edit_name') is-invalid @enderror"
                                    wire:model.defer="edit_name">
                                @error('edit_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>NUP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('edit_nup') is-invalid @enderror"
                                    wire:model.defer="edit_nup">
                                @error('edit_nup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('edit_email') is-invalid @enderror"
                                    wire:model.defer="edit_email">
                                @error('edit_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Department <span class="text-danger">*</span></label>
                                <select class="form-control @error('edit_dept_id') is-invalid @enderror"
                                    wire:model.defer="edit_dept_id">
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('edit_dept_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Company <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('edit_company') is-invalid @enderror"
                                    wire:model.defer="edit_company">
                                @error('edit_company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Role <span class="text-danger">*</span></label>
                                <select class="form-control @error('edit_role_id') is-invalid @enderror"
                                    wire:model.defer="edit_role_id">
                                    <option value="">-- Select Role --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('edit_role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-pill"
                                wire:click="closeRegisterModal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-pill">
                                <i class="fa fa-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // Initialize DataTable
        function initDataTable() {
            let datatable;
            // Clean up existing tooltips first
            $('[data-toggle="tooltip"]').tooltip('dispose');

            // Destroy existing DataTable instance if exists
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            // Initialize new DataTable
            datatable = $('#user-datatable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                drawCallback: function() {
                    // Reinitialize tooltips after each draw/redraw
                    setTimeout(function() {
                        $('[data-toggle="tooltip"]').tooltip({
                            trigger: 'hover',
                            delay: {
                                show: 300,
                                hide: 100
                            }
                        });
                    }, 50);
                }
            });

            // Initial tooltip initialization
            setTimeout(function() {
                $('[data-toggle="tooltip"]').tooltip({
                    trigger: 'hover',
                    delay: {
                        show: 300,
                        hide: 100
                    }
                });
            }, 100);
        }

        // Function to clean up tooltips completely
        function cleanupTooltips() {
            // Remove all tooltip instances
            $('[data-toggle="tooltip"]').each(function() {
                $(this).tooltip('dispose');
            });

            // Remove any lingering tooltip elements
            $('.tooltip').remove();
        }

        // Livewire event listeners
        document.addEventListener('livewire:initialized', () => {
            // User created event
            setTimeout(initDataTable, 100);
            Livewire.on('userCreated', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after user creation
                    setTimeout(initDataTable, 200);
                });
            });

            // User updated event
            Livewire.on('userUpdated', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after update
                    setTimeout(initDataTable, 300);
                });
            });

            // User deleted event
            Livewire.on('userDeleted', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    type: data.type,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after deletion
                    setTimeout(initDataTable, 100);
                });
            });

            window.confirmReset = function(id) {
                swal({
                    title: 'Reset Password?',
                    text: "Password will be reset to the user's NUP.",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-secondary btn-pill'
                        },
                        confirm: {
                            text: 'Yes, reset it!',
                            className: 'btn btn-warning btn-pill'
                        }
                    }
                }).then((willReset) => {
                    if (willReset) {
                        @this.call('resetPassword', id);
                    }
                });
            }

            Livewire.on('userPasswordReset', (event) => {
                const data = event[0];
                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                });
            });

            // Generic alert event
            Livewire.on('showAlert', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: data.icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });

            // Confirm update event
            Livewire.on('confirmUpdate', () => {
                cleanupTooltips(); // Clean up before showing confirmation

                swal({
                    title: 'Confirm Update',
                    text: "Are you sure you want to update this user?",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-secondary btn-pill'
                        },
                        confirm: {
                            text: 'Yes, update it!',
                            className: 'btn btn-primary btn-pill'
                        }
                    }
                }).then((willUpdate) => {
                    if (willUpdate) {

                        @this.call('updateUser');
                    }
                });
            });

            // Modal closed event
            Livewire.on('registerModalClosed', () => {
                // Clean up and reinitialize DataTable when modal is closed
                // alert('Modal closed');
                cleanupTooltips();
                setTimeout(initDataTable, 100);
            });
        }, {
            once: true
        });

        // Delete confirmation function
        window.confirmDelete = function(id) {
            cleanupTooltips(); // Clean up before showing confirmation

            swal({
                title: 'Are you sure?',
                text: "This user will be deleted permanently!",
                icon: 'warning',
                buttons: {
                    cancel: {
                        visible: true,
                        text: 'Cancel',
                        className: 'btn btn-secondary btn-pill'
                    },
                    confirm: {
                        text: 'Yes, delete it!',
                        className: 'btn btn-danger btn-pill'
                    },

                }
            }).then((willDelete) => {
                if (willDelete) {
                    @this.call('delete', id);
                }
            });
        }

        // Handle Livewire navigation events
        document.addEventListener('livewire:navigated', function() {
            cleanupTooltips();
            setTimeout(initDataTable, 100);
            Livewire.on('userCreated', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after user creation
                    setTimeout(initDataTable, 200);
                });
            });

            // User updated event
            Livewire.on('userUpdated', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after update
                    setTimeout(initDataTable, 300);
                });
            });

            // User deleted event
            Livewire.on('userDeleted', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                }).then(() => {
                    // Reinitialize DataTable after deletion
                    setTimeout(initDataTable, 100);
                });
            });

            window.confirmReset = function(id) {
                swal({
                    title: 'Reset Password?',
                    text: "Password will be reset to the user's NUP.",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-secondary btn-pill'
                        },
                        confirm: {
                            text: 'Yes, reset it!',
                            className: 'btn btn-warning btn-pill'
                        }
                    }
                }).then((willReset) => {
                    if (willReset) {
                        @this.call('resetPassword', id);
                    }
                });
            }

            Livewire.on('userPasswordReset', (event) => {
                const data = event[0];
                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: 'btn btn-success btn-pill'
                        }
                    }
                });
            });

            // Generic alert event
            Livewire.on('showAlert', (event) => {
                const data = event[0];
                cleanupTooltips(); // Clean up before showing alert

                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    buttons: {
                        confirm: {
                            className: data.icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });

            // Confirm update event
            Livewire.on('confirmUpdate', () => {
                cleanupTooltips(); // Clean up before showing confirmation

                swal({
                    title: 'Confirm Update',
                    text: "Are you sure you want to update this user?",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-secondary btn-pill'
                        },
                        confirm: {
                            text: 'Yes, update it!',
                            className: 'btn btn-primary btn-pill'
                        }
                    }
                }).then((willUpdate) => {
                    if (willUpdate) {

                        @this.call('updateUser');
                    }
                });
            });

            // Modal closed event
            Livewire.on('registerModalClosed', () => {
                // Clean up and reinitialize DataTable when modal is closed
                // alert('Modal closed');
                cleanupTooltips();
                setTimeout(initDataTable, 100);
            });
        }, {
            once: true
        });

        // Handle any DOM updates from Livewire
        // document.addEventListener('livewire:load', function() {
        //     cleanupTooltips();
        //     setTimeout(initDataTable, 300);
        // }, {
        //     once: true
        // });

        // Clean up tooltips when the page is about to unload
        window.addEventListener('beforeunload', function() {
            cleanupTooltips();
        }, {
            once: true
        });
    </script>
@endpush

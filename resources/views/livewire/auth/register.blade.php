{{-- filepath: resources/views/livewire/auth/register.blade.php --}}
<div class="row mt--2">
    <div class="col-md-6">
        <div class="card full-height">
            <div class="card-body">
                <div class="card-title">Register</div>
                <form wire:submit.prevent="register">
                    @csrf

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" wire:model="name"
                            placeholder="Enter your name" value="{{ old('name') }}" required>
                        @error('name')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="dept_id">Department ID</label>
                        <input type="number" class="form-control" id="dept_id" wire:model="dept_id"
                            placeholder="Enter department ID" value="{{ old('dept_id') }}" required>
                        @error('dept_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nup">NUP</label>
                        <input type="text" class="form-control" id="nup" wire:model="nup"
                            placeholder="Enter NUP" value="{{ old('nup') }}" required>
                        @error('nup')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" wire:model="email"
                            placeholder="Enter your email" value="{{ old('email') }}" required>
                        @error('email')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" class="form-control" id="company" wire:model="company"
                            placeholder="Enter company" value="{{ old('company') }}" required>
                        @error('company')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role ID</label>
                        <input type="number" class="form-control" id="role_id" wire:model="role_id"
                            placeholder="Enter role ID" value="{{ old('role_id') }}" required>
                        @error('role_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="card-title">User List</div>
                <div class="table-responsive">
                    <table id="user-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NUP</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>123456</td>
                                <td>John Doe</td>
                                <td>IT Department</td>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" class="btn btn-link btn-primary btn-lg"
                                            data-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-link btn-danger" data-toggle="tooltip"
                                            title="Delete">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>654321</td>
                                <td>Jane Smith</td>
                                <td>HR Department</td>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" class="btn btn-link btn-primary btn-lg"
                                            data-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-link btn-danger" data-toggle="tooltip"
                                            title="Delete">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#user-datatables').DataTable();
        });
    </script>
@endpush

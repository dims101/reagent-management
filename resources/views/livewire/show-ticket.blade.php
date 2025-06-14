{{-- filepath: resources/views/livewire/show-ticket.blade.php --}}
<div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tickets-table" class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Action</th>
                            <th>Status</th>
                            <th>No SPK</th>
                            <th>Input Date</th>
                            <th>Expected Finish Date</th>
                            <th>Reason</th>
                            <th>Reagent Name</th>
                            <th>Purpose</th>
                            <th>Request by</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>
                                    @if ($ticket->status === 'open')
                                        <div class="row">
                                            <a href="#" class="col text-primary" title="Edit"
                                                wire:click.prevent="editData({{ $ticket->id }})">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="#" class="col text-danger" title="Delete"
                                                wire:click.prevent="deleteData({{ $ticket->id }})">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge
                                        @if ($ticket->status === 'open') badge-danger
                                        @elseif($ticket->status === 'assigned') badge-warning
                                        @elseif($ticket->status === 'closed') badge-success @endif"
                                        @if ($ticket->status === 'rejected') style="background-color:#8B0000;color:#fff" @endif>
                                        {{ ucfirst($ticket->status) }} </span>
                                </td>
                                <td>{{ $ticket->spk_no }}</td>
                                <td>{{ $ticket->created_at ? $ticket->created_at->format('d-m-Y') : '-' }}</td>
                                <td>{{ $ticket->expected_date ? $ticket->expected_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ $ticket->purpose ?? '-' }}</td>
                                <td>{{ optional($ticket->reagent)->name ?? '-' }}</td>
                                <td>{{ $ticket->purpose ?? '-' }}</td>
                                <td>{{ optional($ticket->requester)->name ?? '-' }}</td>
                                <td>{{ optional($ticket->requester->department)->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    @if ($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Edit Ticket</h5>
                        <button type="button" class="close text-white" wire:click="closeEditModal">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- SPK No (readonly) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No SPK</label>
                                <input type="text" class="form-control"
                                    value="{{ $tickets->where('id', $editTicketId)->first()->spk_no ?? '' }}" readonly>
                            </div>

                            <!-- Input Date (readonly) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Input Date</label>
                                <input type="text" class="form-control"
                                    value="{{ $tickets->where('id', $editTicketId)->first()->created_at?->format('d-m-Y') ?? '' }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quantity & UoM -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity & UoM</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model="quantity"
                                        placeholder="Quantity">
                                    <div class="input-group-append">
                                        <select class="form-control" wire:model="uom" style="max-width: 100px;">
                                            <option value="UoM">UoM</option>
                                            <option value="pillow">Pillow</option>
                                            <option value="mg">mg</option>
                                            <option value="mL">mL</option>
                                        </select>
                                    </div>
                                </div>
                                @error('quantity')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- User (readonly) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User</label>
                                <input type="text" class="form-control"
                                    value="{{ $tickets->where('id', $editTicketId)->first()->requester->name ?? '' }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Expected Finish Date -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expected Finish Date</label>
                                <input type="date" class="form-control" wire:model="expected_finish_date">
                                @error('expected_finish_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Reagent Name with Search -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reagent Name</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control"
                                        wire:model.live.debounce.300ms="reagentSearch"
                                        wire:focus="$set('showReagentDropdown', true)" wire:blur="hideReagentDropdown"
                                        placeholder="Search reagents..." autocomplete="off">

                                    @if ($showReagentDropdown && count($reagents) > 0)
                                        <div class="dropdown-menu show position-absolute w-100"
                                            style="max-height: 200px; overflow-y: auto; z-index: 1050;">
                                            @foreach ($reagents as $reagent)
                                                <a href="#"
                                                    class="dropdown-item {{ $reagent_id == $reagent->id ? 'active' : '' }}"
                                                    wire:click.prevent="selectReagent({{ $reagent->id }}, '{{ addslashes($reagent->name) }}')">
                                                    {{ $reagent->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif ($showReagentDropdown && $reagentSearch && count($reagents) == 0)
                                        <div class="dropdown-menu show position-absolute w-100" style="z-index: 1050;">
                                            <span class="dropdown-item-text text-muted">No reagents found</span>
                                        </div>
                                    @endif
                                </div>


                                @error('reagent_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="mb-3">
                            <label class="form-label">Purpose</label>
                            <textarea class="form-control" wire:model="purpose" rows="4" placeholder="Enter purpose..."></textarea>
                            @error('purpose')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="updateTicket">
                            <i class="fa fa-check"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        const allowedStatuses = ['Open', 'Assigned', 'Rejected', 'Closed'];

        function addFilters() {
            if ($('#status-filter').length === 0 && $('#department-filter').length === 0) {
                // Get unique departments from the table
                let departments = new Set();
                $('#tickets-table tbody tr').each(function() {
                    let department = $(this).find('td').eq(9).text().trim();
                    if (department && department !== '-') departments.add(department);
                });

                // Build department select element
                let departmentSelect = $(
                    '<select id="department-filter" class="form-control form-control-sm" style="width:auto; display:inline-block; margin-right:10px;"><option value="">All Departments</option></select>'
                );
                Array.from(departments).sort().forEach(function(department) {
                    departmentSelect.append($('<option>').val(department).text(department));
                });

                // Build status select element with only allowed statuses
                let statusSelect = $(
                    '<select id="status-filter" class="form-control form-control-sm" style="width:auto; display:inline-block; margin-right:10px;"><option value="">All Statuses</option></select>'
                );
                allowedStatuses.forEach(function(status) {
                    statusSelect.append($('<option>').val(status).text(status));
                });

                $('#tickets-table_filter label').before(statusSelect);
                $('#tickets-table_filter label').before(departmentSelect);

                // Department filter logic
                departmentSelect.on('change', function() {
                    let val = $(this).val();
                    let dt = $('#tickets-table').DataTable();
                    if (val) {
                        dt.column(9).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                    } else {
                        dt.column(9).search('', true, false).draw();
                    }
                });

                // Status filter logic
                statusSelect.on('change', function() {
                    let val = $(this).val();
                    let dt = $('#tickets-table').DataTable();
                    if (val) {
                        dt.column(1).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                    } else {
                        dt.column(1).search('', true, false).draw();
                    }
                });
            }
        }

        function initTicketsTable() {
            if ($.fn.DataTable.isDataTable('#tickets-table')) {
                $('#tickets-table').DataTable().destroy();
            }
            $('#tickets-table').DataTable({
                order: [
                    [2, 'desc']
                ],
                language: {
                    emptyTable: "No tickets available"
                },
                columnDefs: [{
                    targets: 1,
                    render: function(data, type, row, meta) {
                        if (type === 'display' || type === 'sort') {
                            return data;
                        }
                        var div = document.createElement("div");
                        div.innerHTML = data;
                        var text = div.textContent || div.innerText || "";
                        return text.trim();
                    }
                }],
                initComplete: function() {
                    addFilters();
                }
            });
        }

        // Initialize DataTable on page load
        // document.addEventListener('DOMContentLoaded', function() {
        //     setTimeout(initTicketsTable, 100);
        // });

        // Initialize DataTable after Livewire navigation
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initTicketsTable, 100);
            // Reinitialize DataTable when modal is closed (after edit)
            Livewire.on('modal-closed', function() {
                setTimeout(initTicketsTable, 100);
            });
        });

        // Hide dropdown with delay to allow click events
        document.addEventListener('hide-dropdown-delayed', function() {
            setTimeout(function() {
                Livewire.dispatch('hide-dropdown-delayed');
            }, 150);
        });

        // Delete confirmation dialog
        document.addEventListener('swal-confirm-delete', function(e) {
            const alertData = e.detail[0];
            swal({
                title: alertData.title,
                text: alertData.text,
                icon: alertData.icon,
                buttons: {
                    cancel: {
                        text: alertData.cancelButtonText || "Cancel",
                        value: false,
                        visible: true,
                        className: "btn btn-secondary btn-pill",
                        closeModal: true,
                    },
                    confirm: {
                        text: alertData.confirmButtonText || "Yes",
                        value: true,
                        visible: true,
                        className: "btn btn-danger btn-pill",
                        closeModal: true
                    }
                }
            }).then(function(result) {
                if (result) {
                    Livewire.dispatch('doDelete');
                }
            });
        });

        // General swal listener for success/error messages
        document.addEventListener('swal', function(e) {
            const alertData = e.detail[0];
            swal({
                title: alertData.title,
                text: alertData.text,
                icon: alertData.icon,
                button: "OK"
            }); // Reinitialize table after any changes
            setTimeout(initTicketsTable, 100);
        });
    </script>
@endpush

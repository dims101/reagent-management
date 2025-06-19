{{-- filepath: resources/views/livewire/assign-ticket.blade.php --}}
<div>
    <div class="card">
        <div class="card-header">
            <h3>Ticket Reagent - Open</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="assign-ticket-table" class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Action</th>
                            <th>Status</th>
                            <th>SPK Number</th>
                            <th>Expected Date</th>
                            <th>Reagent Name</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Requested By</th>
                            <th>Assigned To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>
                                    @if (in_array(auth()->user()->role->id ?? auth()->user()->role, [2, 3]))
                                        @if ($ticket->status === 'open')
                                            <a href="#" wire:click.prevent="openModal({{ $ticket->id }})"
                                                title="Edit">
                                                <i class="fas fa-edit text-primary"></i>
                                            </a>
                                        @endif
                                        @if ($ticket->status === 'assigned')
                                            @if (in_array(auth()->user()->role->id ?? auth()->user()->role, [2, 3]))
                                                <a href="#" wire:click.prevent="closeTicket({{ $ticket->id }})"
                                                    title="Close">
                                                    <i class="fas fa-check text-success"></i>
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                    @if (auth()->user()->id == $ticket->assigned_to)
                                        @if ($ticket->status === 'assigned')
                                            <a href="#" wire:click.prevent="closeTicket({{ $ticket->id }})"
                                                title="Close">
                                                <i class="fas fa-check text-success"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge
                                    @if ($ticket->status === 'open') badge-danger
                                    @elseif($ticket->status === 'assigned') badge-warning
                                    @elseif($ticket->status === 'closed') badge-success
                                    @elseif($ticket->status === 'rejected')" style="background-color:#8B0000; color:#fff; @endif">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->spk_no }}</td>
                                <td>{{ $ticket->expected_date ? $ticket->expected_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ optional($ticket->reagent)->name ?? '-' }}</td>
                                <td>{{ $ticket->request_qty . ' ' . $ticket->uom ?? '' }}
                                </td>
                                <td>{{ $ticket->created_at ? $ticket->created_at->format('d-m-Y') : '-' }}</td>
                                <td>{{ optional($ticket->requester)->name ?? '-' }}</td>
                                <td>{{ optional($ticket->assignedTo)->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No tickets available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="modal fade show" style="display:block;" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form wire:submit.prevent="assign" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Assign Ticket</h5>
                            <button type="button" class="close" wire:click="closeModal">
                                <span class="text-white">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                {{-- Left Column --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No SPK <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" wire:model.defer="spk_no" readonly>
                                        @error('spk_no')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Expected Finish Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model.defer="expected_date"
                                            readonly>
                                        @error('expected_date')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Reagent Name</label>
                                        <input type="text" class="form-control"
                                            value="{{ $reagents[$reagent_id] ?? '-' }}" readonly>
                                        @error('reagent_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    @if ($attachment)
                                        <div class="form-group">
                                            <label>Attachment</label>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ $attachment instanceof \Livewire\TemporaryUploadedFile ? '#' : asset('storage/' . $attachment) }}"
                                                    target="_blank" class="btn btn-link">
                                                    <i class="fas fa-paperclip"></i>
                                                    @php
                                                        if ($attachment instanceof \Livewire\TemporaryUploadedFile) {
                                                            $ext = $attachment->getClientOriginalExtension();
                                                        } elseif (is_string($attachment)) {
                                                            $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                                                        } else {
                                                            $ext = '';
                                                        }
                                                    @endphp
                                                    attachment{{ $ext ? '.' . $ext : '' }}
                                                </a>

                                                <button type="button" class="btn btn-danger btn-pill btn-sm ml-2"
                                                    wire:click="removeAttachment">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <label>Attachment (Optional)</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="customFile"
                                                    wire:model="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                <label class="custom-file-label" for="customFile">Choose file</label>
                                            </div>
                                            <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG (Max:
                                                10MB)</small>
                                            @error('attachment')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            {{-- Show loading indicator when uploading --}}
                                            <div wire:loading wire:target="attachment" class="mt-2">
                                                <small class="text-info">
                                                    <i class="fas fa-spinner fa-spin"></i> Uploading...
                                                </small>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model.defer="start_date"
                                            min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        @error('start_date')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Right Column --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Input Date</label>
                                        <input type="date" class="form-control" wire:model.defer="input_date"
                                            readonly>
                                        @error('input_date')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Expected Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control" wire:model.defer="expected_reason" rows="3"
                                            placeholder="Enter reason for expected date..."></textarea>
                                        @error('expected_reason')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Purpose</label>
                                        <textarea class="form-control" wire:model.defer="purpose" rows="3" readonly></textarea>
                                        @error('purpose')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Assign To <span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.defer="assign_to">
                                            <option value="">Select User</option>
                                            @foreach ($users as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('assign_to')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Deadline Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model.defer="deadline_date"
                                            min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        @error('deadline_date')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Reject Reason Section --}}
                            @if ($showRejectReason)
                                <div class="form-group mt-3">
                                    <label>Reject Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" wire:model.defer="reject_reason" rows="3"
                                        placeholder="Please provide reason for rejection..." maxlength="500"></textarea>
                                    <small class="text-muted">Maximum 500 characters</small>
                                    @error('reject_reason')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    <div class="mt-3 text-right">
                                        <button type="button" class="btn btn-secondary btn-pill ml-2"
                                            wire:click="$set('showRejectReason', false)">
                                            Cancel
                                        </button>
                                        <button type="button" class="btn btn-danger btn-pill"
                                            wire:click="confirmReject">
                                            <i class="fas fa-times"></i> Confirm Reject
                                        </button>

                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-pill" wire:click="closeModal">
                                <i class="fas fa-times"></i> Close
                            </button>

                            @if (!$showRejectReason)
                                <button type="button" class="btn btn-danger btn-pill" wire:click="showRejectInput">
                                    <i class="fas fa-ban"></i> Reject
                                </button>

                                <button type="submit" class="btn btn-success btn-pill" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-check"></i> Assign
                                    </span>
                                    <span wire:loading>
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    </span>
                                </button>
                            @endif
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
        function initAssignTicketTable() {
            if ($.fn.DataTable.isDataTable('#assign-ticket-table')) {
                $('#assign-ticket-table').DataTable().destroy();
            }
            $('#assign-ticket-table').DataTable({
                order: [
                    [6, 'desc']
                ], // Order by Request Date
                language: {
                    emptyTable: "No tickets available"
                },
                pageLength: 25,
                responsive: true
            });
        }

        document.addEventListener('livewire:navigated', function() {
            setTimeout(initAssignTicketTable, 100);
            Livewire.on('modal-closed', function() {
                setTimeout(initAssignTicketTable, 100);
            });
            document.addEventListener('swal-confirm', function(e) {

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
                    if (alertData.confirmButtonText && alertData.confirmButtonText.toLowerCase()
                        .includes('remove')) {
                        Livewire.dispatch('doRemoveAttachment');
                    } else {
                        Livewire.dispatch('doReject');
                    }
                });
            });

            document.addEventListener('swal', function(e) {
                const alertData = e.detail[0];
                swal({
                    title: alertData.title,
                    text: alertData.text,
                    icon: alertData.icon,
                    className: "btn btn-success btn-pill",
                    button: "OK"
                });
            });

            document.addEventListener('swal-confirm-close', function(e) {
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
                            className: "btn btn-success btn-pill",
                            closeModal: true
                        }
                    }
                }).then(function(result) {
                    if (result) {
                        Livewire.dispatch('doClose');
                    }
                });
            });
        }, {
            once: true
        });

        document.addEventListener('livewire:initialized', function() {
            setTimeout(initAssignTicketTable, 100);
        }, {
            once: true
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Update file input label when file is selected
            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('custom-file-input')) {
                    var fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
                    var label = e.target.nextElementSibling;
                    label.innerHTML = fileName;
                }
            });
        }, {
            once: true
        });
    </script>
@endpush

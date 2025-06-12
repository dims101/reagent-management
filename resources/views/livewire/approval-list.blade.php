<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div>
    <div class="row mt--2">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped table-hover datatable">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Status Approval</th>
                                    <th>Request No</th>
                                    <th>Request Date</th>
                                    <th>Requester</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @php
                                    $filteredApprovals = $approvals;
                                    if (auth()->user()->role_id == 2) {
                                        $filteredApprovals = collect($approvals)->where('status', 'waiting manager');
                                    }
                                @endphp

                                @forelse($filteredApprovals as $approval)
                                    <tr>
                                        <td>
                                            <div class="row">
                                                <div class="col-2 text-right mr-0">
                                                    @if (
                                                        (auth()->user()->role_id == 2 && $approval['status'] === 'waiting manager') ||
                                                            (auth()->user()->role_id == 3 && $approval['status'] === 'pending'))
                                                        <a href="#" class="me-2 text-primary"
                                                            title="Approve/Reject"
                                                            wire:click.prevent="openApprovalModal('{{ $approval['request_no'] }}')">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="col-8">
                                                    <h5 class="mb-0"><strong>{{ $approval['requester'] }}</strong>
                                                    </h5>
                                                    <span
                                                        class="text-white badge
                                                        @if ($approval['status'] === 'pending') bg-warning
                                                        @elseif($approval['status'] === 'waiting manager') bg-info
                                                        @elseif($approval['status'] === 'rejected') bg-danger
                                                        @elseif($approval['status'] === 'approved') bg-success
                                                        @else bg-secondary @endif
                                                    ">
                                                        {{ ucfirst($approval['status']) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $approval['request_no'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($approval['request_date'])->format('d-m-Y') }}</td>
                                        <td>{{ $approval['requester'] }}</td>
                                        <td>
                                            <a href="#" class="text-info mr-2" title="View Detail"
                                                wire:click.prevent="openDetailModal('{{ $approval['request_no'] }}')">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No approval requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    @if ($showApprovalModal && $selectedRequest)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog"
            aria-labelledby="approvalModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center" id="approvalModalLabel">
                            Approval Action
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request-no" class="form-label">Request No</label>
                                    <input type="text" class="form-control" id="request-no"
                                        value="{{ $selectedRequest['request_no'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="request-quantity" class="form-label">Request Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="request-quantity"
                                            value="{{ $selectedRequest['request_qty'] ?? '' }}" readonly>
                                        <div class="input-group-append">
                                            <span
                                                class="input-group-text">{{ $selectedRequest['quantity_uom'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="purpose" class="form-label">Purpose</label>
                                    <textarea class="form-control" id="purpose" rows="3" readonly>{{ $selectedRequest['purpose'] ?? '' }}</textarea>
                                </div>

                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request-date" class="form-label">Request Date</label>
                                    <input type="text" class="form-control" id="request-date"
                                        value="{{ isset($selectedRequest['request_date']) ? \Carbon\Carbon::parse($selectedRequest['request_date'])->format('d-m-Y') : '' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="requester" class="form-label">Requester</label>
                                    <input type="text" class="form-control" id="requester"
                                        value="{{ $selectedRequest['requester_name'] ?? '' }}" readonly>
                                </div>


                                <div class="form-group">
                                    <label for="reject_reason" class="form-label">Reject Reason</label>
                                    <textarea class="form-control @error('rejectReason') is-invalid @enderror" id="reject_reason" wire:model="rejectReason"
                                        placeholder="Enter a reason (required for rejection)" rows="3"></textarea>
                                    @error('rejectReason')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="approval_reason" class="form-label">Approval Reason</label>
                                    <textarea class="form-control  @error('approvalReason') is-invalid @enderror" id="approval_reason"
                                        wire:model="approvalReason" placeholder="Enter a reason (required for approval)" rows="3"></textarea>
                                    @error('approvalReason')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="closeModal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger btn-pill" wire:click="rejectRequest"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmReject">
                                <i class="fa fa-ban"></i> Reject Request
                            </span>
                            <span wire:loading wire:target="confirmReject">
                                <i class="fa fa-spinner fa-spin"></i> Processing...
                            </span>
                        </button>
                        <button type="button" class="btn btn-success btn-pill" wire:click="approveRequest"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmApprove">
                                <i class="fa fa-check"></i> Approve Request
                            </span>
                            <span wire:loading wire:target="confirmApprove">
                                <i class="fa fa-spinner fa-spin"></i> Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal Backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif
    {{-- End Approval Modal --}}

    {{-- Detail Modal --}}
    @if ($showDetailModal && $selectedApproval)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog"
            aria-labelledby="detailModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title w-100 text-center" id="detailModalLabel">
                            Request Detail
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="detail-request-no" class="form-label">Request No</label>
                                    <input type="text" class="form-control" id="detail-request-no"
                                        value="{{ $selectedApproval['request_no'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="detail-reagent-name" class="form-label">Reagent Name</label>
                                    <input type="text" class="form-control" id="detail-reagent-name"
                                        value="{{ $selectedApproval['reagent_name'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="detail-purpose" class="form-label">Purpose of Requesting</label>
                                    <textarea class="form-control" id="detail-purpose" rows="3" readonly>{{ $selectedApproval['purpose'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="detail-available-qty" class="form-label">Available Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="detail-available-qty"
                                            value="{{ $selectedApproval['remaining_qty'] ?? '' }}" readonly>
                                        <div class="input-group-append">
                                            <span
                                                class="input-group-text">{{ $selectedApproval['quantity_uom'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="detail-request-date" class="form-label">Request Date</label>
                                    <input type="text" class="form-control" id="detail-request-date"
                                        value="{{ isset($selectedApproval['request_date']) ? \Carbon\Carbon::parse($selectedApproval['request_date'])->format('d-m-Y') : '' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="detail-request-quantity" class="form-label">Request Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="detail-request-quantity"
                                            value="{{ $selectedApproval['request_qty'] ?? '' }}" readonly>
                                        <div class="input-group-append">
                                            <span
                                                class="input-group-text">{{ $selectedApproval['quantity_uom'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="detail-requester" class="form-label">Requester</label>
                                    <input type="text" class="form-control" id="detail-requester"
                                        value="{{ $selectedApproval['requester_name'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="detail-status" class="form-label">Status</label>
                                    <input type="text" class="form-control" id="detail-status"
                                        value="{{ ucfirst($selectedApproval['approval_status'] ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill"
                            wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
    {{-- End Detail Modal --}}
</div>
@push('scripts')
    <script>
        initDataTable = function() {
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
                // alert('code triggered');
            }
            $('.datatable').DataTable({
                "pageLength": 10,
                "responsive": true,
                "order": [
                    [3, "asc"]
                ], // Order by request date descending
            });
        };
        document.addEventListener('livewire:init', function() {
            // Listen for confirmation events
            Livewire.on('modal-opened', () => {
                document.body.classList.add('modal-open');
            });

            Livewire.on('modal-closed', () => {

                document.body.classList.remove('modal-open');
                setTimeout(initDataTable, 100);
            });

            Livewire.on('confirm-approve', (event) => {
                // Handle both array and object data formats
                const data = Array.isArray(event) ? event[0] : event;
                swal({
                    title: "Are you sure?",
                    text: "Do you want to approve this request?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            visible: true,
                            className: "btn btn-secondary btn-pill",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, approve it!",
                            visible: true,
                            className: "btn btn-success btn-pill",
                            closeModal: true
                        }
                    }
                }).then((isConfirm) => {
                    if (isConfirm) {
                        // Call the Livewire method with the request_no
                        @this.call('confirmApprove', data.request_no);
                    }
                });
            });

            Livewire.on('confirm-reject', (event) => {
                // Handle both array and object data formats
                const data = Array.isArray(event) ? event[0] : event;
                swal({
                    title: "Are you sure?",
                    text: "Do you want to reject this request?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            visible: true,
                            className: "btn btn-secondary btn-pill",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, reject it!",
                            visible: true,
                            className: "btn btn-danger btn-pill",
                            closeModal: true
                        }
                    }
                }).then(function(isConfirm) {
                    if (isConfirm) {
                        // Call the Livewire method with request_no and reason
                        @this.call('confirmReject', data.request_no, data.rejectReason);
                    }
                });
            });

            // Listen for approval updates
            Livewire.on('approvalUpdated', () => {
                console.log('Approval list updated');
                // Reinitialize DataTable if needed
                setTimeout(() => {
                    initDataTable;
                }, 100);
            });

            // Listen for SweetAlert events
            Livewire.on('swal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                swal({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                    button: {
                        text: "OK",
                        className: "btn btn-primary btn-pill"
                    }
                });
            });
        }, {
            once: true
        });
        // Uncomment the following lines if you want to initialize DataTable on Livewire initialization

        // document.addEventListener('livewire:initialized', function() {
        //     // Initialize DataTable
        //     alert('Livewire initialized');
        //     $('.datatable').DataTable({
        //         "order": [
        //             [2, "asc"]
        //         ], // Order by request date ascending
        //         "pageLength": 10,
        //         "responsive": true
        //     });
        // });

        document.addEventListener('livewire:navigated', function(event) {
            // Handle navigation events if needed
            setTimeout(initDataTable, 100);
            Livewire.on('modal-opened', () => {
                document.body.classList.add('modal-open');
            });

            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
                setTimeout(initDataTable, 100);
            });
            Livewire.on('confirm-approve', (event) => {
                // Handle both array and object data formats
                const data = Array.isArray(event) ? event[0] : event;

                swal({
                    title: "Are you sure?",
                    text: "Do you want to approve this request?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            visible: true,
                            className: "btn btn-secondary btn-pill",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, approve it!",
                            visible: true,
                            className: "btn btn-success btn-pill",
                            closeModal: true
                        }
                    }
                }).then((isConfirm) => {
                    if (isConfirm) {
                        // Call the Livewire method with the request_no
                        @this.call('confirmApprove', data.request_no);
                    }
                });
            });

            Livewire.on('confirm-reject', (event) => {
                // Handle both array and object data formats
                const data = Array.isArray(event) ? event[0] : event;

                swal({
                    title: "Are you sure?",
                    text: "Do you want to reject this request?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            visible: true,
                            className: "btn btn-secondary btn-pill",
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, reject it!",
                            visible: true,
                            className: "btn btn-danger btn-pill",
                            closeModal: true
                        }
                    }
                }).then(function(isConfirm) {
                    if (isConfirm) {
                        // Call the Livewire method with request_no and reason
                        @this.call('confirmReject', data.request_no, data.reason);
                    }
                });
            });

            // Listen for approval updates
            Livewire.on('approvalUpdated', () => {
                console.log('Approval list updated');
                // Reinitialize DataTable if needed
                if ($.fn.DataTable.isDataTable('.datatable')) {
                    $('.datatable').DataTable().destroy();
                }
                setTimeout(() => {
                    initDataTable;
                }, 100);
            });

            // Listen for SweetAlert events
            Livewire.on('swal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                swal({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                    button: {
                        text: "OK",
                        className: "btn btn-primary btn-pill"
                    }
                });
            });
        }, {
            once: true
        });

        // $(document).ready(function() {
        //     alert('Document is ready');
        //     $('.datatable').DataTable({
        //         "order": [
        //             [2, "asc"]
        //         ], // Order by request date descending
        //         "pageLength": 10,
        //         "responsive": true
        //     });
        // });
        document.addEventListener('DOMContentLoaded', function() {
            initDataTable();
        }, {
            once: true
        });
    </script>
@endpush

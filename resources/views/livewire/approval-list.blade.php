<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="card full-height">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-2">Show</label>
                                <select wire:model.live="perPage" class="form-control form-control-sm"
                                    style="width: 80px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="mb-0 ms-2">entries</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    class="form-control form-control-sm" placeholder="Search..." style="width: 250px;">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="display table table-striped table-hover datatable">
                            <thead class="thead-light text-center">
                                <tr>

                                    <th wire:click="sortBy('status')" style="cursor: pointer;">
                                        Status Approval
                                        @if ($sortField === 'status')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </th>

                                    <th wire:click="sortBy('request_no')" style="cursor: pointer;">
                                        Request No
                                        @if ($sortField === 'request_no')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </th>

                                    <th wire:click="sortBy('request_date')" style="cursor: pointer;">
                                        Request Date
                                        @if ($sortField === 'request_date')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </th>

                                    <th wire:click="sortBy('requester')" style="cursor: pointer;">
                                        Requester
                                        @if ($sortField === 'requester')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('requested_to')" style="cursor: pointer;">
                                        Requested to
                                        @if ($sortField === 'requested_to')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </th>

                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">

                                @php
                                    $filteredApprovals = $approvals;

                                    if (auth()->user()->role_id == 2 && auth()->user()->id == 21) {
                                        $filteredApprovals = collect($approvals->items())
                                            ->where('status', 'waiting manager')
                                            ->where('requested_to')
                                            ->values();
                                    }
                                @endphp


                                @forelse($filteredApprovals as $approval)
                                    <tr>
                                        <td>
                                            <div class="row">
                                                <div class="col-2 text-right mr-0">
                                                    @if (
                                                        (auth()->user()->role_id == 2 && auth()->user()->id == 21 && $approval['status'] === 'waiting manager') ||
                                                            (auth()->user()->role_id == 3 && auth()->user()->id == 37 && $approval['status'] === 'pending'))
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
                                        <td>{{ \Carbon\Carbon::parse($approval['request_date'])->format('d-m-Y') }}
                                        </td>
                                        <td>{{ $approval['requester'] }}</td>
                                        <td>{{ $approval['requested_to'] }}</td>
                                        <td>
                                            <a href="#" class="text-info mr-2" title="View Detail"
                                                wire:click.prevent="openDetailModal('{{ $approval['request_no'] }}')">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No approval requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                        <div>
                            @if ($approvals instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                Showing {{ $approvals->firstItem() ?? 0 }} to {{ $approvals->lastItem() ?? 0 }}
                                of {{ $approvals->total() }} entries
                                @if (!empty($search))
                                    <span class="text-muted">(filtered from total entries)</span>
                                @endif
                            @else
                                Showing {{ $approvals->count() }} entries
                            @endif
                        </div>
                        <div>
                            @if ($approvals instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                {{ $approvals->links() }}
                            @endif
                        </div>
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
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                        <div class="row">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request-no" class="form-label">Request No</label>
                                    <input type="text" class="form-control" id="request-no"
                                        value="{{ $selectedRequest['request_no'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="reagent-name" class="form-label">Reagent Name</label>
                                    <textarea class="form-control" id="reagent-name" rows="2" readonly>{{ $selectedRequest['reagent_name'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="purpose" class="form-label">Purpose of Requesting</label>
                                    <textarea class="form-control" id="purpose" rows="3" readonly>{{ $selectedRequest['purpose'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="available-qty" class="form-label">Available Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="available-qty"
                                            value="{{ isset($selectedRequest['remaining_qty']) ? rtrim(rtrim(strval($selectedRequest['remaining_qty']), '0'), '.') : '' }}"
                                            readonly>
                                        <div class="input-group-append">
                                            <span
                                                class="input-group-text">{{ $selectedRequest['quantity_uom'] ?? '' }}</span>
                                        </div>
                                    </div>
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
                                    <label for="request-quantity" class="form-label">Request Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="request-quantity"
                                            value="{{ isset($selectedRequest['request_qty']) ? rtrim(rtrim(strval($selectedRequest['request_qty']), '0'), '.') : '' }}"
                                            readonly>
                                        <div class="input-group-append">
                                            <span
                                                class="input-group-text">{{ $selectedRequest['quantity_uom'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="requester" class="form-label">Requester</label>
                                    <input type="text" class="form-control" id="requester"
                                        value="{{ $selectedRequest['requester_name'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="customer" class="form-label">Customer</label>
                                    <input type="text" class="form-control" id="customer"
                                        value="{{ $selectedRequest['customer_name'] ?? '' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="detail-status" class="form-label">Status</label>
                                    <input type="text" class="form-control" id="detail-status"
                                        value="{{ ucfirst($selectedRequest['approval_status'] ?? '') }}" readonly>
                                </div>

                                {{-- Approval / Reject reason areas remain controlled by $showApprovalReason / $showRejectReason --}}
                                @if ($showApprovalReason)
                                    <div class="form-group">
                                        <label for="approval_reason" class="form-label">Approval Reason <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control @error('approvalReason') is-invalid @enderror" id="approval_reason"
                                            wire:model="approvalReason" placeholder="Enter a reason (optional)" rows="3"></textarea>
                                        @error('approvalReason')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endif

                                @if ($showRejectReason)
                                    <div class="form-group">
                                        <label for="reject_reason" class="form-label">Reject Reason <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control @error('rejectReason') is-invalid @enderror" id="reject_reason"
                                            wire:model="rejectReason" placeholder="Enter a reason (required for rejection)" rows="3"></textarea>
                                        @error('rejectReason')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="closeModal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger btn-pill" wire:click="rejectRequest"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="rejectRequest,confirmReject">
                                <i class="fa fa-ban"></i>
                                @if ($showRejectReason)
                                    Confirm Reject
                                @else
                                    Reject Request
                                @endif
                            </span>
                            <span wire:loading wire:target="rejectRequest">
                                <i class="fa fa-spinner fa-spin"></i> Processing...
                            </span>
                            <span wire:loading wire:target="confirmReject">
                                <i class="fa fa-spinner fa-spin"></i> Submitting...
                            </span>
                        </button>
                        <button type="button" class="btn btn-success btn-pill" wire:click="approveRequest"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="approveRequest,confirmApprove">
                                <i class="fa fa-check"></i>
                                Approve Request
                            </span>
                            <span wire:loading wire:target="approveRequest">
                                <i class="fa fa-spinner fa-spin"></i> Processing...
                            </span>
                            <span wire:loading wire:target="confirmApprove">
                                <i class="fa fa-spinner fa-spin"></i> Submitting...
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
                                    <textarea class="form-control" id="detail-reagent-name" rows="2" readonly>{{ $selectedApproval['reagent_name'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="detail-purpose" class="form-label">Purpose of Requesting</label>
                                    <textarea class="form-control" id="detail-purpose" rows="3" readonly>{{ $selectedApproval['purpose'] ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="detail-available-qty" class="form-label">Available Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="detail-available-qty"
                                            value="{{ isset($selectedApproval['remaining_qty']) ? rtrim(rtrim(strval($selectedApproval['remaining_qty']), '0'), '.') : '' }}"
                                            readonly>
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
                                            value="{{ isset($selectedApproval['request_qty']) ? rtrim(rtrim(strval($selectedApproval['request_qty']), '0'), '.') : '' }}"
                                            readonly>
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
                                    <label for="customer" class="form-label">Customer</label>
                                    <input type="text" class="form-control" id="customer"
                                        value="{{ $selectedApproval['customer_name'] ?? '' }}" readonly>
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
        document.addEventListener('livewire:initialized', function() {

            // Listen for modal opened event
            Livewire.on('modal-opened', () => {
                document.body.classList.add('modal-open');
            });

            // Listen for modal closed event
            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
            });

            // Listen for confirm approve event
            Livewire.on('confirm-approve', (event) => {
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
                        @this.call('confirmApprove', data.request_no);
                    }
                });
            });

            // Listen for confirm reject event
            Livewire.on('confirm-reject', (event) => {
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
                        @this.call('confirmReject', data.request_no, data.rejectReason);
                    }
                });
            });

            // Listen for approval updated event
            Livewire.on('approvalUpdated', () => {
                console.log('Approval list updated');
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
    </script>
@endpush

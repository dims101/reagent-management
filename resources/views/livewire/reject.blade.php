<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover table-striped display datatable text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Request No</th>
                                <th>Request Date</th>
                                <th>Requester</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rejects as $reject)
                                <tr>
                                    <td>{{ $reject->request_no }}</td>
                                    <td>{{ date('d-m-Y', strtotime($reject->request_date)) }}</td>
                                    <td>{{ $reject->requester }}</td>
                                    <td>
                                        <a href="#"
                                            @click.prevent="$dispatch('modal-open', { request_no: '{{ $reject->request_no }}' })"
                                            data-toggle="modal" data-target="#modalDetail" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Modal Detail --}}
        <!-- Modal -->
        <div class="modal" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true"
            wire:ignore>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalDetailLabel">
                            Reject Request
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Request No.</label>
                                    <input type="text" class="form-control font-italic"
                                        wire:model="detail_request_no" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Purpose of Requesting</label>
                                    <textarea class="form-control font-italic" wire:model="detail_purpose" readonly></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Quantity Of Selected Reagent</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-italic"
                                            wire:model="detail_request_qty" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text" wire:model="detail_quantity_uom"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Reject Reason</label>
                                    <textarea class="form-control font-italic" wire:model="detail_reason" readonly></textarea>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Request Date</label>
                                    <input type="text" class="form-control font-italic"
                                        wire:model="detail_request_date" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Request Quantity</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-italic"
                                            wire:model="detail_request_qty" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text" wire:model="detail_quantity_uom"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Requester</label>
                                    <input type="text" class="form-control font-italic" wire:model="detail_requester"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal" x-data
                            x-on:click="$dispatch('modal-closed')">Close</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- End Modal Detail --}}
        @push('scripts')
            <script>
                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('.datatable')) {
                        $('.datatable').DataTable().destroy();
                    }
                    $('.datatable').DataTable({
                        "order": [
                            [1, "asc"]
                        ],
                        "language": {
                            "emptyTable": "No data available in table"
                        }
                    });
                }

                $(document).ready(function() {
                    initDataTable();
                });

                window.addEventListener('livewire:navigated', function() {
                    setTimeout(initDataTable, 300);
                });

                window.addEventListener('livewire:initialized', function() {
                    setTimeout(initDataTable, 300);
                });
                window.addEventListener('modal-closed', function() {
                    setTimeout(initDataTable, 300);
                });
            </script>
        @endpush
    </div>

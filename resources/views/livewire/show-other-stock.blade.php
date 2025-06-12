<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-body" wire:ignore>
                <table id="stock-datatable" class="display table table-striped table-hover datatable" wire:ignore>
                    <thead class="thead-light">
                        <tr>
                            <th>Action</th>
                            <th>Reagent Name</th>
                            <th>Maker</th>
                            <th>No Catalog</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Expired Date</th>
                            <th>Owner</th>
                            <th>Location</th>
                            <th>Site</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks as $stock)
                            <tr>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" class="btn btn-link btn-primary btn-lg"
                                            wire:click="openRequestModal({{ $stock->id }})" title="Request Stock">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $stock->reagent_name }}</td>
                                <td>{{ $stock->maker }}</td>
                                <td>{{ $stock->catalog_no }}</td>
                                <td>{{ $stock->remaining_qty }}</td>
                                <td>{{ $stock->quantity_uom }}</td>
                                <td data-order="{{ $stock->expired_date }}">
                                    {{ date('d-m-Y', strtotime($stock->expired_date)) }}</td>
                                <td>{{ $stock->department ? $stock->department->name : '-' }}</td>
                                <td>{{ $stock->location }}</td>
                                <td>{{ $stock->site }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Livewire Modal --}}
    @if ($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog"
            aria-labelledby="stockRequestModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title w-100 text-center" id="stockRequestModalLabel">
                            Request Stock - {{ $selectedStock ? $selectedStock->reagent_name : '' }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="submitRequest">
                        <div class="modal-body">
                            <div class="row">
                                {{-- Left Column --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="request-no" class="form-label">Request No <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="request-no"
                                            wire:model="request_no" placeholder="Enter request number" readonly
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="purpose" class="form-label">Purpose of Requesting <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="purpose" wire:model="purpose" rows="3" placeholder="Enter purpose of request"
                                            required></textarea>
                                        @error('purpose')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="selected-reagent-qty" class="form-label">Available Quantity <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="selected-reagent-qty"
                                                value="{{ $selectedStock ? $selectedStock->remaining_qty : 0 }}"
                                                readonly required>
                                            <div class="input-group-append">
                                                <span
                                                    class="input-group-text">{{ $selectedStock ? $selectedStock->quantity_uom : '' }}</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <span class="text-danger">*</span>Available quantity from selected reagent
                                        </small>
                                    </div>
                                </div>

                                {{-- Right Column --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="request-date" class="form-label">Request Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="request-date"
                                            value="{{ date('Y-m-d') }}" readonly required>
                                    </div>

                                    <div class="form-group">
                                        <label for="request-quantity" class="form-label">Request Quantity <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="request-quantity"
                                                wire:model="request_qty" min="0.01" step="0.01"
                                                max="{{ $selectedStock ? $selectedStock->remaining_qty : 0 }}"
                                                placeholder="Enter requested quantity" required>
                                            <div class="input-group-append">
                                                <span
                                                    class="input-group-text">{{ $selectedStock ? $selectedStock->quantity_uom : '' }}</span>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Quantity you want to request</small>
                                        @error('request_qty')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="requester" class="form-label">Requester <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="requester" readonly
                                            value="{{ auth()->user()->name }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-pill" wire:click="closeModal">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-pill" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fa fa-check"></i> Submit Request
                                </span>
                                <span wire:loading>
                                    <i class="fa fa-spinner fa-spin"></i> Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Modal Backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif
</div>

@push('scripts')
    <script>
        function initDataTable() {
            let stockDatatable;
            $('[data-toggle="tooltip"]').tooltip('dispose');
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }
            stockDatatable = $('#stock-datatable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [6, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: 0
                    },
                    {
                        targets: 6,
                        type: 'date'
                    }
                ],
                dom: '<"row"<"col-sm-6"l><"col-sm-3"<"reagent-filter">><"col-sm-3"f>>rtip',
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function() {
                    var reagentFilter = $(
                        '<select class="form-control"><option value="">Reagent Name</option></select>');
                    var reagentNames = [];
                    this.api().column(1).data().unique().sort().each(function(value) {
                        if (value && reagentNames.indexOf(value) === -1) {
                            reagentNames.push(value);
                            reagentFilter.append('<option value="' + value + '">' + value +
                                '</option>');
                        }
                    });
                    $('div.reagent-filter').html(reagentFilter);
                    reagentFilter.on('change', function() {
                        var val = $(this).val();
                        stockDatatable.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
                    });
                }
            });
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

        function cleanupTooltips() {
            $('[data-toggle="tooltip"]').each(function() {
                $(this).tooltip('dispose');
            });
            $('.tooltip').remove();
        }

        document.addEventListener('DOMContentLoaded', function() {
            initDataTable();
        }, {
            once: true
        });

        document.addEventListener('livewire:initialized', function() {
            // alert('Livewire initialized');
            Livewire.on('modal-opened', () => {
                document.body.classList.add('modal-open');
            });

            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
                cleanupTooltips();
                setTimeout(initDataTable, 100);
            });

            Livewire.on('request-submitted', () => {
                setTimeout(() => {
                    swal({
                        title: 'Success!',
                        text: 'Request submitted successfully.',
                        icon: 'success',
                        buttons: {
                            confirm: {
                                className: 'btn btn-success btn-pill'
                            }
                        }
                    });
                    cleanupTooltips();
                    initDataTable();
                }, 400);
            });

            Livewire.on('swal', (data) => {
                swal({
                    title: data.title,
                    text: data.text,
                    type: data.icon || data.type || 'info',
                    buttons: {
                        confirm: {
                            className: data.icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });
        }, {
            once: true
        });

        document.addEventListener('livewire:navigated', function() {
            // alert('Livewire navigated');
            cleanupTooltips();
            setTimeout(initDataTable, 100);
            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
                setTimeout(initDataTable, 100);
            });
            Livewire.on('request-submitted', () => {
                setTimeout(() => {
                    swal({
                        title: 'Success!',
                        text: 'Request submitted successfully.',
                        icon: 'success',
                        buttons: {
                            confirm: {
                                className: 'btn btn-success btn-pill'
                            }
                        }
                    });
                    cleanupTooltips();
                    initDataTable();
                }, 400);
            });
            Livewire.on('swal', (data) => {
                swal({
                    title: data.title,
                    text: data.text,
                    type: data.icon || data.type || 'info',
                    buttons: {
                        confirm: {
                            className: data.icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });
        }, {
            once: true
        });

        // document.addEventListener('livewire:load', function() {
        //     cleanupTooltips();
        //     setTimeout(initDataTable, 300);
        // });

        window.addEventListener('beforeunload', function() {
            cleanupTooltips();
        }, {
            once: true
        });

        Livewire.hook('message.processed', (message, component) => {
            setTimeout(() => {
                cleanupTooltips();
                initDataTable();
                shouldInitDataTable = false;
            }, 100);


        }, {
            once: true
        });
    </script>
@endpush

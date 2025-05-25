<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-body">
                <table id="stock-datatable" class="display table table-striped table-hover datatable" wire:ignore>
                    <thead class="table-secondary">
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
                                        <button type="button" class="btn btn-link btn-primary btn-lg" data-toggle="modal"
                                            data-target="#stockRequestModal" data-stock-id="{{ $stock->id }}"
                                            data-reagent-name="{{ $stock->reagent_name }}"
                                            data-remaining-qty="{{ $stock->remaining_qty }}"
                                            data-uom="{{ $stock->quantity_uom }}" title="Request Stock">
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

    {{-- Stock Request Modal --}}
    <div class="modal fade" id="stockRequestModal" tabindex="-1" aria-labelledby="stockRequestModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title w-100 text-center" id="stockRequestModalLabel">Request Stock</h5>
                    <button type="button " class="close" data-dismiss="modal" aria-label="Close">
                        <span class="text-white" aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="stockRequestForm" wire:submit.prevent="submitRequest">
                    <div class="modal-body">
                        <div class="row">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <input class="reagent_id" type="hidden" wire:model='reagent_id'>
                                <div class="form-group">
                                    <label for="request-no" class="form-label">Request No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="request-no" wire:model="request_no"
                                        placeholder="Enter request number" readonly required>
                                </div>

                                <div class="form-group">
                                    <label for="purpose" class="form-label">Purpose of Requesting <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="purpose" wire:model="purpose" rows="3" placeholder="Enter purpose of request"
                                        required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="selected-reagent-qty" class="form-label">Quantity of Selected
                                        Reagent <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="selected-reagent-qty" readonly
                                            required>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="reagent-uom"></span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted"> <span class="text-danger">*</span>Available
                                        quantity from selected reagent</small>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request-date" class="form-label">Request Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="request-date" readonly required>
                                </div>
                                <div class="form-group">
                                    <label for="request-quantity" class="form-label">Request Quantity <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="request-quantity"
                                            wire:model="request_qty" min="1" step="0.01"
                                            placeholder="Enter requested quantity" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="request-uom"></span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Quantity you want to request</small>
                                </div>

                                <div class="form-group">
                                    <label for="requester" class="form-label">Requester <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="requester" "
                                        readonly value="{{ auth()->user()->name }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden fields --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-pill" wire:loading.attr="disabled">
                            <span wire:loading.remove>Submit Request</span>
                            <span wire:loading><i class="fa fa-spinner fa-spin"></i> Submitting...</span>
                        </button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
    {{-- End Stock Request Modal --}}
</div>

@push('scripts')
    <script>
        // Function to initialize the DataTable
        function initDataTable() {
            let stockDatatable;
            // Clean up existing tooltips first
            $('[data-toggle="tooltip"]').tooltip('dispose');

            // Destroy existing DataTable instance if exists
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            // Initialize new DataTable
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
                columnDefs: [{
                    orderable: false,
                    targets: 0
                }],
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
                    // Add reagent name filter
                    var reagentFilter = $(
                        '<select class="form-control"><option value="">Reagent Name</option></select>');

                    // Get unique reagent names
                    var reagentNames = [];
                    this.api().column(1).data().unique().sort().each(function(value) {
                        if (value && reagentNames.indexOf(value) === -1) {
                            reagentNames.push(value);
                            reagentFilter.append('<option value="' + value + '">' + value +
                                '</option>');
                        }
                    });

                    // Place the filter in the designated div
                    $('div.reagent-filter').html(reagentFilter);

                    // Add event listener for the filter
                    reagentFilter.on('change', function() {
                        var val = $(this).val();
                        stockDatatable.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
                    });
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
        //end initDataTable

        // Function to clean up tooltips completely
        function cleanupTooltips() {
            // Remove all tooltip instances
            $('[data-toggle="tooltip"]').each(function() {
                $(this).tooltip('dispose');
            });

            // Remove any lingering tooltip elements
            $('.tooltip').remove();
        }

        // Handle Livewire navigation events
        document.addEventListener('livewire:navigated', function() {
            cleanupTooltips();
            setTimeout(initDataTable, 300);
        });

        // Handle any DOM updates from Livewire
        document.addEventListener('livewire:load', function() {
            cleanupTooltips();
            setTimeout(initDataTable, 300);
        });

        // Stock Request Modal Logic
        $('#stockRequestModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var stockId = button.data('stock-id');
            var reagentName = button.data('reagent-name');
            var remainingQty = button.data('remaining-qty');
            var uom = button.data('uom');

            var modal = $(this);

            // Update modal title
            modal.find('.modal-title').text('Request Stock - ' + reagentName);
            modal.find('.reagent_id').val(stockId);
            // Populate form fields
            modal.find('#stock-id').val(stockId);
            modal.find('#reagent-name').val(reagentName);
            modal.find('#selected-reagent-qty').val(remainingQty);
            modal.find('#reagent-uom').text(uom);
            modal.find('#request-uom').text(uom);

            // Set current date as default
            var today = new Date().toISOString().split('T')[0];
            modal.find('#request-date').val(today);

            // Set max value for request quantity
            modal.find('#request-quantity').attr('max', remainingQty);
        });

        // Reset form when modal is hidden
        $('#stockRequestModal').on('hidden.bs.modal', function() {
            $('#stockRequestForm')[0].reset();
        });

        // Handle form submission
        // $('#submitRequest').on('click', function() {
        //     var formData = {
        //         stock_id: $('#stock-id').val(),
        //         reagent_name: $('#reagent-name').val(),
        //         request_no: $('#request-no').val(),
        //         purpose: $('#purpose').val(),
        //         selected_reagent_qty: $('#selected-reagent-qty').val(),
        //         request_date: $('#request-date').val(),
        //         request_qty: $('#request-quantity').val(),
        //         requester: $('#requester').val()
        //     };

        //     // Basic validation
        //     if (!formData.request_no) {
        //         alert('Please enter a request number');
        //         return;
        //     }

        //     if (!formData.request_qty || formData.request_qty <= 0) {
        //         alert('Please enter a valid request quantity');
        //         return;
        //     }

        //     if (parseFloat(formData.request_qty) > parseFloat(formData.selected_reagent_qty)) {
        //         alert('Request quantity cannot exceed available quantity');
        //         return;
        //     }

        //     // Here you can add your AJAX call to submit the form
        //     console.log('Form Data:', formData);

        //     // Example AJAX call (uncomment and modify as needed):
        //     /*
        //     $.ajax({
        //         url: '/stock-request',
        //         method: 'POST',
        //         data: formData,
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(response) {
        //             $('#stockRequestModal').modal('hide');
        //             // Show success message
        //             alert('Stock request submitted successfully!');
        //             // Optionally refresh the datatable
        //             location.reload();
        //         },
        //         error: function(xhr) {
        //             alert('Error submitting request. Please try again.');
        //         }
        //     });
        //     */

        //     // For now, just close the modal and show success
        //     // $('#stockRequestModal').modal('hide');
        //     // alert('Stock request submitted successfully!');
        // });
    </script>
@endpush

{{-- filepath: resources/views/livewire/create-stock.blade.php --}}
<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-body">
                <form wire:submit.prevent="saveStock">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Input Date (created_at) -->
                            <div class="form-group">
                                <label for="input_date" class="form-label">
                                    Input Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('input_date') is-invalid @enderror"
                                    id="input_date" wire:model.defer="input_date" max="{{ date('Y-m-d') }}" readonly
                                    required>
                                @error('input_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- PO Number -->
                            <div class="form-group">
                                <label for="po_no" class="form-label">PO Number</label>
                                <input type="text" class="form-control @error('po_no') is-invalid @enderror"
                                    id="po_no" wire:model.defer="po_no" placeholder="Enter PO Number"
                                    maxlength="50">
                                @error('po_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Reagent Name -->
                            <div class="form-group">
                                <label for="reagent_name" class="form-label">
                                    Reagent Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('reagent_name') is-invalid @enderror"
                                    id="reagent_name" wire:model.defer="reagent_name" placeholder="Enter Reagent Name"
                                    maxlength="100" required>
                                @error('reagent_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Maker -->
                            <div class="form-group">
                                <label for="maker" class="form-label">Maker</label>
                                <input type="text" class="form-control @error('maker') is-invalid @enderror"
                                    id="maker" wire:model.defer="maker" placeholder="Enter Maker" maxlength="100">
                                @error('maker')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- No. Catalog -->
                            <div class="form-group">
                                <label for="catalog_no" class="form-label">Catalog Number</label>
                                <input type="text" class="form-control @error('catalog_no') is-invalid @enderror"
                                    id="catalog_no" wire:model.defer="catalog_no" placeholder="Enter Catalog Number"
                                    maxlength="100">
                                @error('catalog_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Expired Date -->
                            <div class="form-group">
                                <label for="expired_date" class="form-label">
                                    Expiry Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('expired_date') is-invalid @enderror"
                                    id="expired_date" wire:model.defer="expired_date"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                @error('expired_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Quantity & UoM -->
                            <div class="form-group">
                                <label class="form-label">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01"
                                        class="form-control @error('initial_qty') is-invalid @enderror" id="initial_qty"
                                        wire:model="initial_qty" placeholder="Enter Quantity" required>
                                    <select class="form-control ml-2 @error('quantity_uom') is-invalid @enderror"
                                        wire:model="quantity_uom" style="max-width: 100px;">
                                        <option value="">UoM</option>
                                        <option value="pillow">pillow</option>
                                        <option value="g">g</option>
                                        <option value="mg">mg</option>
                                    </select>
                                </div>
                                @error('initial_qty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('quantity_uom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Location -->
                            <div class="form-group">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                    id="location" wire:model.defer="location"
                                    placeholder="e.g., Freezer A1, Cabinet B2" maxlength="100">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Lead Time -->
                            <div class="form-group">
                                <label for="lead_time" class="form-label">Lead Time of Receiving</label>
                                <div class="input-group">
                                    <input type="number" min="0"
                                        class="form-control @error('lead_time') is-invalid @enderror" id="lead_time"
                                        wire:model.defer="lead_time" placeholder="Enter Lead Time">
                                    <div class="input-group-text">Days</div>
                                </div>
                                @error('lead_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Owner (Autofill) -->
                            <div class="form-group">
                                <label for="owner_display" class="form-label">Owner</label>
                                <input type="text" class="form-control bg-light" id="owner_display"
                                    value="{{ $owner_name }}" readonly>
                                {{-- <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Auto-filled from logged in user
                                </small> --}}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Minimum Stock - Fixed Case 2 & 3 -->
                            <div class="form-group">
                                <label for="minimum_qty" class="form-label">Minimum Stock Alert</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('minimum_qty') is-invalid @enderror"
                                        id="minimum_qty" wire:model="minimum_qty" placeholder="Enter Minimum Stock">
                                    <div class="input-group-text">
                                        <span>{{ $quantity_uom ?: 'Qty' }}</span>
                                    </div>
                                </div>
                                @error('minimum_qty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Alert when stock falls below this level
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Site -->
                            <div class="form-group">
                                <label for="site" class="form-label">Site</label>
                                <input type="text" class="form-control @error('site') is-invalid @enderror"
                                    id="site" wire:model.defer="site" placeholder="Enter Site/Building"
                                    maxlength="100">
                                @error('site')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-success btn-pill" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-check"></i> Save Stock
                                    </span>
                                    <span wire:loading>
                                        <i class="fas fa-spinner fa-spin"></i> Saving...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // Enhanced SweetAlert event listener for success
            Livewire.on('swal', (data) => {
                console.log('SweetAlert triggered:', data); // Debug log

                const alertData = data[0];

                // Different configurations based on icon type
                if (alertData.icon === 'success') {
                    swal({
                        icon: alertData.icon,
                        title: alertData.title,
                        text: alertData.text,
                        buttons: false,
                    }).then(() => {
                        // Optional: Reload page or redirect after success
                        // window.location.reload();
                    });
                } else {
                    // For error and other types
                    swal({
                        icon: alertData.icon,
                        title: alertData.title,
                        text: alertData.text,
                        button: {
                            text: "OK",
                            value: true,
                            visible: true,
                            className: "btn btn-primary btn-pill"
                        }
                    });
                }
            });

            // Alternative success handler
            // Livewire.on('stock-created-successfully', () => {
            //     console.log('Stock created successfully!'); // Debug log

            //     // Fallback success notification
            //     swal({
            //         icon: 'success',
            //         title: 'Success!',
            //         text: 'Stock has been added successfully!',
            //         buttons: false
            //     });

            //     // Optional: Show bootstrap notify as well
            //     if (typeof $.notify !== 'undefined') {
            //         $.notify({
            //             icon: 'fa fa-check',
            //             message: 'Stock has been added successfully!'
            //         }, {
            //             type: 'success',
            //             timer: 1000,
            //             placement: {
            //                 from: 'bottom',
            //                 align: 'right'
            //             }
            //         });
            //     }
            // });

            // Fix Case 3: Real-time validation error alert
            Livewire.on('show-validation-error', (data) => {
                console.log('Validation error:', data); // Debug log

                // Show a subtle toast notification for real-time validation
                if (typeof $.notify !== 'undefined') {
                    $.notify({
                        icon: 'fa fa-exclamation-triangle',
                        message: data[0].message
                    }, {
                        type: 'warning',
                        timer: 1000,
                        placement: {
                            from: 'bottom',
                            align: 'right'
                        }
                    });
                } else {
                    // Fallback to alert if notify is not available
                    alert(data[0].message);
                }
            });
        }, {
            once: true
        });

        // Additional validation check on form submit
        document.addEventListener('livewire:navigated', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const minQty = parseFloat(document.getElementById('minimum_qty').value) || 0;
                    const initialQty = parseFloat(document.getElementById('initial_qty').value) || 0;

                    if (minQty > initialQty && initialQty > 0) {
                        e.preventDefault();
                        swal({
                            icon: 'error',
                            title: 'Validation Error!',
                            text: 'Minimum quantity cannot be greater than initial quantity.',
                            button: {
                                text: "OK",
                                value: true,
                                visible: true,
                                className: "btn btn-primary btn-pill"
                            }
                        });
                        return false;
                    }
                });
            }
            Livewire.on('swal', (data) => {
                console.log('SweetAlert triggered:', data); // Debug log

                const alertData = data[0];

                // Different configurations based on icon type
                if (alertData.icon === 'success') {
                    swal({
                        icon: alertData.icon,
                        title: alertData.title,
                        text: alertData.text,
                        buttons: false,
                    }).then(() => {
                        // Optional: Reload page or redirect after success
                        // window.location.reload();
                    });
                } else {
                    // For error and other types
                    swal({
                        icon: alertData.icon,
                        title: alertData.title,
                        text: alertData.text,
                        button: {
                            text: "OK",
                            value: true,
                            visible: true,
                            className: "btn btn-primary btn-pill"
                        }
                    });
                }
            });
        });
    </script>
@endpush

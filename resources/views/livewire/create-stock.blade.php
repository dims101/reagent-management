{{-- filepath: resources/views/livewire/create-stock.blade.php --}}
<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-body">
                <form wire:submit.prevent="confirmSaveStock">
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
                                <label for="po_no" class="form-label">PO Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('po_no') is-invalid @enderror"
                                    id="po_no" wire:model.defer="po_no" placeholder="Enter PO Number" maxlength="50"
                                    required>
                                @error('po_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Reagent Name with Server-side Search -->
                            <div class="form-group position-relative">
                                <label for="reagent_search" class="form-label">
                                    Reagent Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control @error('reagent_name') is-invalid @enderror"
                                        id="reagent_search" wire:model.live.debounce.300ms="search"
                                        placeholder="Type to search reagent..." autocomplete="off">
                                    @if ($search)
                                        <button type="button" class="btn btn-outline-secondary"
                                            wire:click="clearSearch">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Dropdown Results -->
                                @if ($showDropdown && count($reagents) > 0)
                                    <div class="dropdown-menu show position-absolute w-100 mt-1"
                                        style="z-index: 1050; max-height: 300px; overflow-y: auto;">
                                        @foreach ($reagents as $reagent)
                                            <a href="#"
                                                class="dropdown-item d-flex justify-content-between align-items-center"
                                                wire:click.prevent="selectReagent({{ $reagent['id'] }})">
                                                <div>
                                                    <strong>{{ $reagent['name'] }}</strong>
                                                    @if ($reagent['catalog_no'])
                                                        <br><small class="text-muted">Catalog:
                                                            {{ $reagent['catalog_no'] }}</small>
                                                    @endif
                                                    @if ($reagent['vendor'])
                                                        <br><small class="text-muted">Vendor:
                                                            {{ $reagent['vendor'] }}</small>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @elseif($showDropdown && $search && count($reagents) === 0)
                                    <div class="dropdown-menu show position-absolute w-100 mt-1" style="z-index: 1050;">
                                        <div class="dropdown-item-text text-muted">
                                            <i class="fas fa-search"></i> No reagents found for "{{ $search }}"
                                        </div>
                                    </div>
                                @endif

                                <!-- Hidden input to store selected reagent name for validation -->
                                <input type="hidden" wire:model="reagent_name">

                                @error('reagent_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                @if ($search && strlen($search) < 2 && strlen($search) > 0)
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Type at least 2 characters to search
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Maker -->
                            <div class="form-group">
                                <label for="maker" class="form-label">
                                    Maker <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control text-uppercase @error('maker') is-invalid @enderror"
                                    id="maker" wire:model.defer="maker" placeholder="Enter Maker" maxlength="100"
                                    style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"
                                    required>
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
                                <label for="catalog_no" class="form-label">
                                    Catalog Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('catalog_no') is-invalid @enderror"
                                    id="catalog_no" wire:model.defer="catalog_no" placeholder="Enter Catalog Number"
                                    maxlength="100" required>
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
                                        class="form-control @error('initial_qty') is-invalid @enderror"
                                        id="initial_qty" wire:model="initial_qty" placeholder="Enter Quantity"
                                        required>
                                    <select class="form-control ml-2 @error('quantity_uom') is-invalid @enderror"
                                        wire:model="quantity_uom" style="max-width: 100px;" required>
                                        <option value="">UoM</option>
                                        <option value="pillow">pillow</option>
                                        <option value="mg">mg</option>
                                        <option value="mL">mL</option>
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
                                <label for="location" class="form-label">
                                    Location <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                    id="location" wire:model.defer="location"
                                    placeholder="e.g., Freezer A1, Cabinet B2" maxlength="100" required>
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
                                <label for="lead_time" class="form-label">
                                    Lead Time of Receiving <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" min="0"
                                        class="form-control @error('lead_time') is-invalid @enderror" id="lead_time"
                                        wire:model.defer="lead_time" placeholder="Enter Lead Time" required>
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
                                <label for="owner_display" class="form-label">
                                    Owner <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" id="owner_display"
                                    value="{{ $owner_name }}" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Minimum Stock - Fixed Case 2 & 3 -->
                            <div class="form-group">
                                <label for="minimum_qty" class="form-label">
                                    Minimum Stock Alert <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('minimum_qty') is-invalid @enderror"
                                        id="minimum_qty" wire:model="minimum_qty" placeholder="Enter Minimum Stock"
                                        required>
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
                                <label for="site" class="form-label">
                                    Site <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('site') is-invalid @enderror"
                                    id="site" wire:model.defer="site" placeholder="Enter Site/Building"
                                    maxlength="100" required>
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

            // SweetAlert confirmation handler
            Livewire.on('swal-confirm', (data) => {
                const alertData = data[0];
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
                        Livewire.dispatch('doSaveStock');
                    }
                });
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const reagentSearch = document.getElementById('reagent_search');
                const dropdown = document.querySelector('.dropdown-menu.show');

                if (dropdown && reagentSearch && !reagentSearch.contains(event.target) && !dropdown
                    .contains(event.target)) {
                    Livewire.dispatch('hideDropdown');
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

            // Re-bind Livewire event listeners after navigation
            Livewire.on('swal', (data) => {
                const alertData = data[0];
                if (alertData.icon === 'success') {
                    swal({
                        icon: alertData.icon,
                        title: alertData.title,
                        text: alertData.text,
                        buttons: false,
                    });
                } else {
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

            Livewire.on('swal-confirm', (data) => {
                const alertData = data[0];
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
                        Livewire.dispatch('doSaveStock');
                    }
                });
            });
        }, {
            once: true
        });
    </script>
@endpush

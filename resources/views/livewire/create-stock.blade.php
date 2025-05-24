{{-- filepath: resources/views/livewire/create-stock.blade.php --}}
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    Input Stock
                </h4>
            </div>
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
                                        wire:model.defer="initial_qty" placeholder="Enter Quantity" required>
                                    <select class="form-control ml-2 @error('quantity_uom') is-invalid @enderror"
                                        wire:model.defer="quantity_uom" style="max-width: 100px;">
                                        <option value="">UoM</option>
                                        <option value="ml">ml</option>
                                        <option value="g">g</option>
                                        <option value="pcs">pcs</option>
                                        <!-- Tambahkan UoM lain sesuai kebutuhan -->
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
                                <label for="location" class="form-label">Storage Location</label>
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
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Auto-filled from logged in user
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Minimum Stock -->
                            <div class="form-group">
                                <label for="minimum_qty" class="form-label">Minimum Stock Alert</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('minimum_qty') is-invalid @enderror"
                                        id="minimum_qty" wire:model="minimum_qty" placeholder="Enter Minimum Stock">
                                    <div class="input-group-text">
                                        <span wire:ignore>{{ $quantity_uom ?: 'Qty' }}</span>
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
                                <label for="site" class="form-label">Site/Building</label>
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
                                <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
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
            Livewire.on('swal', (data) => {
                swal({
                    icon: data[0].icon,
                    title: data[0].title,
                    text: data[0].text,
                    button: {
                        text: "OK",
                        value: true,
                        visible: true,
                        className: "btn btn-primary"
                    }
                });
            });
        });
    </script>
@endpush

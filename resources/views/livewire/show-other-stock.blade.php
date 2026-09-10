<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div class="row mt--2">
    <div class="col-md-12">
        <div class="card full-height">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-6">
                        <label>Show
                            <select wire:model.live="perPage" class="form-control form-control-sm"
                                style="width: 80px; display: inline-block;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            entries
                        </label>
                    </div>
                    <div class="col-sm-3">
                        <select wire:model.live="reagentFilter" class="form-control form-control-sm">
                            <option value="">Filter Reagents</option>
                            @foreach ($reagentList as $reagent)
                                <option value="{{ $reagent }}">{{ $reagent }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" wire:model.live.debounce.500ms="search"
                            class="form-control form-control-sm" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                {{-- <div wire:loading class="text-center py-3">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div> --}}
                <table class="display table table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Action</th>
                            <th>Reagent Name</th>
                            <th>Maker</th>
                            <th>No Catalog</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th wire:click="sortBy('expired_date')" style="cursor: pointer;">Expired Date
                                @if ($sortField === 'expired_date')
                                    <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>Owner</th>
                            <th>Location</th>
                            <th>Site</th>
                            <th>Input By</th>
                            <th>No. Po</th>
                            <th>No. Lot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks as $stock)
                            <tr>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" class="btn btn-link btn-primary"
                                            wire:click="openRequestModal({{ $stock->id }})" title="Request Stock">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $stock->reagent_name }}</td>
                                <td>{{ $stock->maker }}</td>
                                <td>{{ $stock->catalog_no }}</td>
                                <td class="text-right">
                                    {{ floor($stock->remaining_qty) != $stock->remaining_qty
                                        ? number_format($stock->remaining_qty, 2, ',', '.')
                                        : number_format($stock->remaining_qty, 0, ',', '.') }}
                                </td>
                                <td>{{ $stock->quantity_uom }}</td>
                                <td data-order="{{ $stock->expired_date }}" style="min-width: 80px;">
                                    {{ date('d-m-Y', strtotime($stock->expired_date)) }}</td>
                                <td>{{ $stock->department ? $stock->department->name : '-' }}</td>
                                <td>{{ $stock->location }}</td>
                                <td>{{ $stock->site }}</td>
                                <td>{{ $stock->input_by ? $stock->inputBy->name : '-' }}</td>
                                <td>{{ $stock->po_no }}</td>
                                <td>{{ $stock->no_lot }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info">
                            Showing {{ $stocks->firstItem() ?? 0 }} to {{ $stocks->lastItem() ?? 0 }}
                            of {{ $stocks->total() }} entries
                        </div>
                    </div>
                    <div class="col-sm-7">
                        {{ $stocks->links() }}
                    </div>
                </div>
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

                                    {{-- Purpose Field with Search --}}
                                    <div class="form-group">
                                        <label for="purpose" class="form-label">Purpose of Requesting <span
                                                class="text-danger">*</span></label>

                                        @if (!$isNewPurpose)
                                            <div class="position-relative">
                                                <input type="text" class="form-control" id="purpose-search"
                                                    wire:model.live.debounce.300ms="purposeSearch"
                                                    wire:focus="showPurposeOptions" wire:blur="hidePurposeDropdown"
                                                    placeholder="Search or select purpose..." autocomplete="off"
                                                    required>

                                                {{-- Purpose Dropdown --}}
                                                @if ($showPurposeDropdown)
                                                    <div class="position-absolute w-100 bg-white border border-top-0 shadow-sm"
                                                        style="top: 100%; z-index: 1050; max-height: 200px; overflow-y: auto;">
                                                        @if (count($purposeOptions) > 0)
                                                            @foreach ($purposeOptions as $option)
                                                                <div class="px-3 py-2 cursor-pointer hover-bg-light border-bottom"
                                                                    wire:click="selectPurpose({{ $option['id'] }}, '{{ $option['name'] }}')"
                                                                    style="cursor: pointer;">
                                                                    {{ $option['name'] }}
                                                                </div>
                                                            @endforeach
                                                        @endif

                                                        {{-- Add New Purpose Option --}}
                                                        @if (!empty($purposeSearch))
                                                            <div class="px-3 py-2 cursor-pointer hover-bg-light border-bottom text-primary"
                                                                wire:click="addNewPurpose" style="cursor: pointer;">
                                                                <i class="fa fa-plus-circle"></i> Add new purpose:
                                                                "{{ $purposeSearch }}"
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            {{-- New Purpose Input --}}
                                            <div class="card border-primary">
                                                {{-- <div class="card-header bg-primary text-white py-2">
                                                    <small><i class="fa fa-plus-circle"></i> New Purpose Name</small>
                                                </div> --}}
                                                <div class="card-body p-3">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label small">New Purpose Name</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model="newPurposeName"
                                                            placeholder="Enter new purpose name..." required>
                                                        @error('newPurposeName')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button" class="btn btn-sm btn-success btn-pill"
                                                            wire:click="saveNewPurpose">
                                                            <i class="fa fa-plus-circle"></i> Add
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-secondary mr-2 btn-pill"
                                                            wire:click="cancelNewPurpose">
                                                            Cancel
                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @error('purpose')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="customer" class="form-label">Customer <span
                                                class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control" id="customer"
                                                wire:model.live.debounce.300ms="customerSearch"
                                                wire:focus="focusCustomerField" wire:blur="hideCustomerDropdown"
                                                placeholder="Search or select customer" autocomplete="off" required>

                                            {{-- Customer Dropdown --}}
                                            @if ($showCustomerDropdown && !$showAddNewCustomer)
                                                <div class="position-absolute w-100 border rounded shadow-sm bg-white"
                                                    style="z-index: 1000; max-height: 200px; overflow-y: auto; top: 100%;">
                                                    @if (!empty($customers))
                                                        @foreach ($customers as $customerItem)
                                                            <div class="dropdown-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                                                wire:click="selectCustomer({{ $customerItem['id'] }}, '{{ $customerItem['name'] }}')"
                                                                style="cursor: pointer;">
                                                                {{ $customerItem['name'] }}
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Add New Customer Option --}}
                                                    @if ($customerSearch && !collect($customers)->contains('name', $customerSearch))
                                                        <div class="dropdown-item px-3 py-2 cursor-pointer text-primary border-top"
                                                            wire:click="showAddNewCustomerForm"
                                                            style="cursor: pointer;">
                                                            <i class="fa fa-plus-circle mr-2"></i>Add new customer :
                                                            "{{ $customerSearch }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Add New Customer Form --}}
                                            @if ($showAddNewCustomer)
                                                <div class="position-absolute w-100 border rounded shadow-sm bg-white p-3"
                                                    style="z-index: 1000; top: 100%;">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label small">New Customer Name</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model="newCustomerName"
                                                            placeholder="Enter new customer name">
                                                        @error('newCustomerName')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button"
                                                            class="btn btn-success btn-sm btn-pill mr-2"
                                                            wire:click="addNewCustomer">
                                                            <i class="fa fa-plus-circle"></i> Add
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-secondary btn-sm btn-pill"
                                                            wire:click="cancelAddNewCustomer">
                                                            <i class="fa fa-times"></i> Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @error('customer')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="selected-reagent-qty" class="form-label">Available Quantity <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="selected-reagent-qty"
                                                value="{{ floor((float) $selectedStock->remaining_qty) == (float) $selectedStock->remaining_qty
                                                    ? number_format((float) $selectedStock->remaining_qty, 0, ',', '.')
                                                    : number_format((float) $selectedStock->remaining_qty, 2, ',', '.') }}"
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
                                            <!-- Input tampil ke user dengan format Indonesia -->
                                            <input type="text" inputmode="decimal"
                                                class="form-control @error('request_qty') is-invalid @enderror"
                                                id="request_qty_display" placeholder="Enter requested quantity"
                                                value="{{ number_format($request_qty, 2, ',', '.') }}"
                                                oninput="formatRequestQty(event)" required>

                                            <!-- Append UOM -->
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    {{ $selectedStock ? $selectedStock->quantity_uom : '' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Hidden input untuk sinkron ke Livewire -->
                                        <input type="hidden" id="request_qty" wire:model="request_qty"
                                            min="0.01"
                                            max="{{ $selectedStock ? $selectedStock->remaining_qty : 0 }}">
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
                            <button type="submit" class="btn btn-success btn-pill" wire:loading.attr="disabled">
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
        // Format Request Qty function
        function formatRequestQty(e) {
            const input = e.target;
            const raw = input.value;

            // Bersihkan karakter selain angka dan koma
            const cleaned = raw.replace(/[^\d,]/g, '');

            // Pisah bagian ribuan & desimal
            const parts = cleaned.split(',');
            const formattedInt = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const formatted = parts.length > 1 ? `${formattedInt},${parts[1]}` : formattedInt;

            // Tampilkan angka terformat ke input tampilan
            input.value = formatted;

            // Ubah ke angka biasa untuk input hidden
            const numeric = cleaned.replace(/\./g, '').replace(',', '.');

            const hiddenInput = document.getElementById('request_qty');
            hiddenInput.value = numeric;
            hiddenInput.dispatchEvent(new Event('input'));
        }

        // Livewire initialized event
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('modal-opened', () => {
                document.body.classList.add('modal-open');
            });

            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
            });

            Livewire.on('hide-purpose-dropdown', () => {
                setTimeout(() => {
                    @this.set('showPurposeDropdown', false);
                }, 200);
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
                }, 400);
            });

            Livewire.on('swal', (data) => {
                swal({
                    title: data[0].title,
                    text: data[0].text,
                    icon: data[0].icon,
                    buttons: {
                        confirm: {
                            className: data[0].icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });
        }, {
            once: true
        });

        // Livewire navigated event
        document.addEventListener('livewire:navigated', function() {
            Livewire.on('modal-closed', () => {
                document.body.classList.remove('modal-open');
            });

            Livewire.on('hide-purpose-dropdown', () => {
                setTimeout(() => {
                    @this.set('showPurposeDropdown', false);
                }, 200);
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
                }, 400);
            });

            Livewire.on('swal', (data) => {
                swal({
                    title: data[0].title,
                    text: data[0].text,
                    icon: data[0].icon,
                    buttons: {
                        confirm: {
                            className: data[0].icon === 'error' ? 'btn btn-danger btn-pill' :
                                'btn btn-success btn-pill'
                        }
                    }
                });
            });
        }, {
            once: true
        });
    </script>

    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .position-relative {
            position: relative;
        }

        .position-absolute {
            position: absolute;
        }
    </style>
@endpush

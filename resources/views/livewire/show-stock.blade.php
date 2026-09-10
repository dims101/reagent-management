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
                            @foreach ($this->reagentList as $reagent)
                                <option value="{{ $reagent }}">{{ $reagent }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" wire:model.live.debounce.1000ms="search"
                            class="form-control form-control-sm" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="display table table-striped table-hover datatable">
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
                        @foreach ($this->stocks as $stock)
                            <tr wire:key="stock-row-{{ $stock->id }}">
                                <td>
                                    <div class="form-button-action d-flex gap-2">
                                        <button type="button" class="btn btn-link btn-primary p-2"
                                            wire:click="openRequestModal({{ $stock->id }})" title="Request Stock">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @if ($stock->input_by === auth()->id())
                                            <button type="button" class="btn btn-link btn-danger p-0"
                                                onclick="confirmDelete({{ $stock->id }})" title="Delete Stock">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
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
                                    {{ date('d-m-Y', strtotime($stock->expired_date)) }}
                                </td>
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
            {{-- Pagination --}}
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info">
                            Showing {{ $this->stocks->firstItem() ?? 0 }} to {{ $this->stocks->lastItem() ?? 0 }}
                            of {{ $this->stocks->total() }} entries
                        </div>
                    </div>
                    <div class="col-sm-7">
                        {{ $this->stocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Livewire Modal --}}
    @if ($showModal)
        <div class="modal fade show" style="display: block;" wire:key="modal-{{ $reagent_id }}" tabindex="-1"
            role="dialog" aria-labelledby="stockRequestModalLabel">
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
                    <form wire:submit.prevent="confirmSubmitRequest">
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
                                        <div class="position-relative">
                                            <input type="text" class="form-control" id="purpose"
                                                wire:model.live.debounce.300ms="purposeSearch"
                                                wire:focus="focusPurposeField" wire:blur="hidePurposeDropdown"
                                                placeholder="Search or select purpose" autocomplete="off" required>

                                            {{-- Purpose Dropdown --}}
                                            @if ($showPurposeDropdown && !$showAddNewPurpose)
                                                <div class="position-absolute w-100 border rounded shadow-sm bg-white"
                                                    wire:key="purpose-dropdown"
                                                    style="z-index: 1000; max-height: 200px; overflow-y: auto; top: 100%;">
                                                    @if (!empty($purposes))
                                                        @foreach ($purposes as $purposeItem)
                                                            <div class="dropdown-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                                                wire:click="selectPurpose({{ $purposeItem['id'] }}, '{{ $purposeItem['name'] }}')"
                                                                style="cursor: pointer;">
                                                                {{ $purposeItem['name'] }}
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    @if ($purposeSearch && !collect($purposes)->contains('name', $purposeSearch))
                                                        <div class="dropdown-item px-3 py-2 cursor-pointer text-primary border-top"
                                                            wire:click="showAddNewPurposeForm"
                                                            style="cursor: pointer;">
                                                            <i class="fa fa-plus-circle mr-2"></i>Add new purpose :
                                                            "{{ $purposeSearch }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Add New Purpose Form --}}
                                            @if ($showAddNewPurpose)
                                                <div class="position-absolute w-100 border rounded shadow-sm bg-white p-3"
                                                    style="z-index: 1000; top: 100%;">
                                                    <div class="form-group mb-2">
                                                        <label class="form-label small">New Purpose Name</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model="newPurposeName"
                                                            placeholder="Enter new purpose name">
                                                        @error('newPurposeName')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button"
                                                            class="btn btn-success btn-sm btn-pill mr-2"
                                                            wire:click="addNewPurpose">
                                                            <i class="fa fa-plus-circle"></i> Add
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-secondary btn-sm btn-pill"
                                                            wire:click="cancelAddNewPurpose">
                                                            <i class="fa fa-times"></i> Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
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
                                            <!-- Display input yang sudah diformat -->
                                            <input type="text" inputmode="decimal"
                                                class="form-control @error('request_qty') is-invalid @enderror"
                                                id="request_qty_display" placeholder="Enter requested quantity"
                                                value="{{ number_format($request_qty, 2, ',', '.') }}"
                                                oninput="formatRequestQty(event)" required>

                                            <!-- Tambahan satuan -->
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    {{ $selectedStock ? $selectedStock->quantity_uom : '' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Hidden input untuk wire:model Livewire -->
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
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('commit', ({
                component,
                commit,
                respond,
                succeed,
                fail
            }) => {
                const beforeSize = JSON.stringify(component.canonical).length;

                succeed(({
                    snapshot,
                    effects
                }) => {
                    const snapshotSize = JSON.stringify(snapshot).length;
                    const effectsSize = JSON.stringify(effects).length;
                    const totalSize = snapshotSize + effectsSize;

                    console.group(`📊 LIVEWIRE REQUEST - ${component.name}`);
                    console.log(`📦 Before Size: ${(beforeSize / 1024).toFixed(2)} KB`);
                    console.log(`📦 Snapshot Size: ${(snapshotSize / 1024).toFixed(2)} KB`);
                    console.log(`📦 Effects Size: ${(effectsSize / 1024).toFixed(2)} KB`);
                    console.log(`📦 Total Response: ${(totalSize / 1024).toFixed(2)} KB`);
                    console.log('---');

                    // ✅ CEK HTML SIZE DETAIL
                    if (effects && effects.html) {
                        const htmlSize = effects.html.length;
                        console.log(`🎨 HTML Size: ${(htmlSize / 1024).toFixed(2)} KB`);

                        // Hitung berapa banyak <tr> di-render
                        const rowCount = (effects.html.match(/<tr/g) || []).length;
                        console.log(`📊 Table Rows: ${rowCount}`);

                        // Cek apakah modal ada di HTML
                        const hasModal = effects.html.includes('modal fade show');
                        console.log(`🪟 Modal Rendered: ${hasModal ? 'YES ❌' : 'NO ✅'}`);

                        // Cek dropdown
                        const hasPurposeDropdown = effects.html.includes('purpose-dropdown');
                        const hasCustomerDropdown = effects.html.includes('customer-dropdown');
                        console.log(
                        `📋 Purpose Dropdown: ${hasPurposeDropdown ? 'YES ❌' : 'NO ✅'}`);
                        console.log(
                            `📋 Customer Dropdown: ${hasCustomerDropdown ? 'YES ❌' : 'NO ✅'}`);

                        // Estimasi size per komponen
                        console.log('---');
                        console.log('📏 Estimated Sizes:');
                        if (rowCount > 0) {
                            console.log(
                                `  - Avg per row: ${(htmlSize / rowCount / 1024).toFixed(2)} KB`
                                );
                        }
                    }

                    if (snapshot && snapshot.data) {
                        console.log('🔍 Snapshot Keys:', Object.keys(snapshot.data));

                        const dataSizes = Object.entries(snapshot.data).map(([key, value]) => ({
                            key,
                            size: JSON.stringify(value).length,
                            sizeKB: (JSON.stringify(value).length / 1024).toFixed(2)
                        })).sort((a, b) => b.size - a.size);

                        console.log('📊 Property Sizes (sorted):');
                        console.table(dataSizes.slice(0, 10));
                    } else {
                        console.warn('⚠️ snapshot.data is undefined');
                    }

                    console.groupEnd();
                });
            });
        });
    </script>
    <script>
        // Confirm delete function
        window.confirmDelete = function(id) {
            swal({
                title: 'Delete Stock?',
                text: "Are you sure you want to delete this stock?",
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Cancel',
                        visible: true,
                        className: 'btn btn-secondary btn-pill'
                    },
                    confirm: {
                        text: 'Yes, delete it!',
                        className: 'btn btn-danger btn-pill'
                    }
                }
            }).then((willDelete) => {
                if (willDelete) {
                    Livewire.dispatch('deleteStock', {
                        id: id
                    });
                }
            });
        }

        // Format Request Qty function
        function formatRequestQty(e) {
            const input = e.target;
            const raw = input.value;

            // Hapus semua karakter selain angka dan koma
            const cleaned = raw.replace(/[^\d,]/g, '');

            // Pisah bagian ribuan dan desimal
            const parts = cleaned.split(',');
            const ribuan = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const formatted = parts.length > 1 ? ribuan + ',' + parts[1] : ribuan;

            input.value = formatted;

            // Convert ke format numerik normal (untuk Livewire)
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
                        Livewire.dispatch('doSubmitRequest');
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
                        Livewire.dispatch('doSubmitRequest');
                    }
                });
            });
        }, {
            once: true
        });
    </script>
@endpush

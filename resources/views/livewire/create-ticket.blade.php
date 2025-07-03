<div>
    <form wire:submit.prevent="submit" class="p-4 bg-white rounded shadow-sm">
        <div class="form-row">
            <!-- No SPK -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">No SPK</label>
                <input type="text" wire:model.defer="spk_no" class="form-control" readonly required>
                @error('no_spk')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <!-- Input Date -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Input Date</label>
                <input type="text" class="form-control" value="{{ now()->format('d-m-Y') }}" readonly required>
                @error('input_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <!-- Quantity & UoM -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Quantity & UoM</label>
                <div class="input-group mb-3">
                    <input type="number" wire:model.defer="quantity" class="form-control" min="1" required
                        placeholder="Quantity">
                    <div class="input-group-append">
                        <select class="form-control" wire:model="uom" required>
                            <option value="">UoM</option>
                            <option value="pillow">pillow</option>
                            <option value="mg">mg</option>
                            <option value="mL">mL</option>
                        </select>
                    </div>
                </div>
                @error('quantity')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                @error('uom')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <!-- User -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">User</label>
                <input type="text" value="{{ auth()->user()->name }}" class="form-control" readonly required>
                @error('user')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <!-- Expected Finish Date -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Expected Finish Date</label>
                <input type="date" wire:model.defer="expected_finish_date" class="form-control"
                    min="{{ now()->format('Y-m-d') }}" required>
                @error('expected_finish_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <!-- Reagent Name -->
            <div class="form-group col-md-6" wire:ignore>
                <label class="font-weight-medium">Reagent Name</label>
                <select id="reagent_name" class="form-control @error('reagent_id') is-invalid @enderror"
                    wire:model.live="reagent_id">
                    <option value="">-- Select Reagent --</option>
                    @foreach ($reagents as $reagent)
                        <option value="{{ $reagent->id }}">{{ $reagent->name }}</option>
                    @endforeach
                </select>
                @error('reagent_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Customer Name with Search -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Customer Name</label>

                @if ($is_adding_new_customer)
                    <!-- Add New Customer Mode -->
                    <div class="input-group">
                        <input type="text" wire:model.live="new_customer_name"
                            class="form-control @error('new_customer_name') is-invalid @enderror"
                            placeholder="Enter new customer name" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success btn-sm" wire:click="addNewCustomer"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="addNewCustomer">Add</span>
                                <span wire:loading wire:target="addNewCustomer">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                </span>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm"
                                wire:click="cancelAddNewCustomer">Cancel</button>
                        </div>
                    </div>
                    @error('new_customer_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                @else
                    <!-- Search Customer Mode -->
                    <div class="position-relative">
                        <input type="text" wire:model.live.debounce.300ms="customer_search"
                            class="form-control @error('customer_id') is-invalid @enderror"
                            placeholder="Search customer or type new name..."
                            wire:focus="$set('show_customer_dropdown', true)" wire:blur="hideCustomerDropdown"
                            autocomplete="off">

                        <!-- Dropdown for search results -->
                        @if ($show_customer_dropdown && (count($customers) > 0 || $customer_search))
                            <div class="dropdown-menu show w-100 mt-1"
                                style="max-height: 200px; overflow-y: auto; z-index: 1050;">
                                @if (count($customers) > 0)
                                    @foreach ($customers as $customer)
                                        <button type="button"
                                            class="dropdown-item d-flex justify-content-between align-items-center"
                                            wire:click="selectCustomer({{ $customer->id }}, '{{ $customer->name }}')">
                                            <span>{{ $customer->name }}</span>
                                            {{-- <small class="text-muted">Existing</small> --}}
                                        </button>
                                    @endforeach
                                    <div class="dropdown-divider"></div>
                                @endif

                                @if ($customer_search && !$customers->contains('name', $customer_search))
                                    <button type="button"
                                        class="dropdown-item d-flex justify-content-between align-items-center text-primary"
                                        wire:click="showAddNewCustomer">
                                        <span>Add "{{ $customer_search }}" as new customer</span>
                                        <small class="text-primary">+ New</small>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    @error('customer_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                @endif

                @if ($selected_customer_name && !$is_adding_new_customer)
                    <small class="text-primary mt-1">Selected: {{ $selected_customer_name }}</small>
                @endif
            </div>

            <!-- Purpose -->
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Purpose</label>
                <textarea wire:model.defer="purpose" class="form-control" rows="4" required></textarea>
                @error('purpose')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-success btn-pill" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <svg class="mr-2" style="width: 20px; height: 20px;" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Submit
                </span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                    Submitting...
                </span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        function initChoices() {
            const reagentSelect = document.getElementById('reagent_name');
            if (reagentSelect && reagentSelect.choices) {
                reagentSelect.choices.destroy();
            }
            new Choices('#reagent_name', {
                searchEnabled: true,
                itemSelectText: '',
            });
        }

        document.addEventListener('livewire:navigated', function() {
            initChoices();

            // SweetAlert handlers
            Livewire.on('swal', function(data) {
                const alertData = data[0];
                if (alertData.then) {
                    swal({
                        title: alertData.title,
                        text: alertData.text,
                        icon: alertData.icon,
                        buttons: alertData.buttons
                    }).then(function(result) {
                        if (result) {
                            Livewire.dispatch(alertData.then);
                        }
                    });
                } else {
                    swal({
                        title: alertData.title,
                        text: alertData.text,
                        icon: alertData.icon,
                        button: false,
                        timer: 2000
                    });
                }
            });

            Livewire.on('swal-success', function(data) {
                const alertData = data[0];
                swal({
                    title: alertData.title,
                    text: alertData.text,
                    icon: alertData.icon,
                    button: false,
                    timer: 2000
                });
            });

            Livewire.on('swal-error', function(data) {
                const alertData = data[0];
                swal({
                    title: alertData.title,
                    text: alertData.text,
                    icon: alertData.icon,
                    button: false,
                    timer: 3000
                });
            });

            // Handle delayed dropdown hiding
            Livewire.on('hide-dropdown-delayed', function() {
                setTimeout(() => {
                    Livewire.dispatch('$set', ['show_customer_dropdown', false]);
                }, 200);
            });
        }, {
            once: true
        });

        // Click outside to close dropdown
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.position-relative')) {
                Livewire.dispatch('$set', ['show_customer_dropdown', false]);
            }
        });
    </script>
@endpush

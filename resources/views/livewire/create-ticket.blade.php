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
                        <input type="text" class="form-control" wire:model="uom" placeholder="UoM" required
                            style="width: 80px;" readonly>
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
            <div class="form-group col-md-6">
                <label class="font-weight-medium">Reagent Name</label>
                <select wire:model.live="reagent_id" class="form-control" required>
                    <option value="">Select Reagent</option>
                    @foreach ($reagents as $id => $reagent_name)
                        <option value="{{ $id }}">{{ $reagent_name }}</option>
                    @endforeach
                </select>
                @error('reagent_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                @if ($remaining_qty)
                    <small class="ml-3 text-muted">**Remaining quantity: {{ $remaining_qty . ' ' . $uom }}</small>
                @endif
            </div>
        </div>
        <!-- Purpose -->
        <div class="form-group mt-4">
            <label class="font-weight-medium">Purpose</label>
            <textarea wire:model.defer="purpose" class="form-control" rows="4" required></textarea>
            @error('purpose')
                <small class="text-danger">{{ $message }}</small>
            @enderror
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
        document.addEventListener('livewire:navigated', function() {
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
                        timer: 100
                    });
                }
            });
            Livewire.on('swal-success', function(data) {
                swal({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                    button: false,
                    timer: 50
                });
            });
        }, {
            once: true
        });
    </script>
@endpush

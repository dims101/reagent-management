<div class="row">
    <div class="col-md-12">
        <div class="card">
            <!-- AUTO COMPLETE DROPDOWN -->
            <div class="form-group">
                <select id="choices-reagent" class="form-control mb-1 px-4 py-4 rounded shadow"
                    wire:model.live="selectedReagent">
                    <option value="">-- Select --</option>
                    @foreach ($reagents as $reagent)
                        <option value="{{ $reagent->id }}">{{ $reagent->name }}</option>
                    @endforeach
                </select>
            </div>
            <br>
            {{ $selectedReagent ? 'Selected Reagent ID: ' . $selectedReagent : 'No reagent selected' }}
            <br>
            {{-- {{ $selectedVendor ? 'Selected Vendor ID: ' . $selectedVendor : 'No vendor selected' }} --}}
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#choices-reagent', {
                searchEnabled: true,
                itemSelectText: '',
            });
        });
    </script>
@endpush

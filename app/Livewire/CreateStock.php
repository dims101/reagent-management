<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Stock;
use App\Models\Reagent;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CreateStock extends Component
{
    #[Title('Input Reagent Stock')]
    public $subTitle = "Input new reagent stock details";
    public $input_date;
    public $po_no;
    public $reagent_name;
    public $reagent_id; // Add this to store selected reagent ID
    public $maker;
    public $catalog_no;
    public $expired_date;
    public $initial_qty;
    public $quantity_uom;
    public $site;
    public $lead_time;
    public $dept_owner_id;
    public $minimum_qty;
    public $location;
    public $input_by;

    // Server-side search properties
    public $search = '';
    public $reagents = [];
    public $showDropdown = false;

    // Add property for owner name display
    public $owner_name;
    public $input_name;

    public function mount()
    {
        $this->input_date = now()->format('Y-m-d');
        $this->dept_owner_id = Auth::user()->dept_id ?? null;
        $this->input_by = Auth::user()->id ?? null;
        $this->owner_name = Auth::user()->department ? Auth::user()->department->name : 'Unknown Department';
        $this->input_name = Auth::user()->name ?? 'Unknown User';

        // Initialize with empty reagents array
        $this->reagents = [];
    }

    // Server-side search method
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) { // Start searching after 2 characters
            $this->reagents = Reagent::select('id', 'name', 'catalog_no', 'vendor')
                ->where('type', 'Stock')
                ->where('name', 'LIKE', '%' . $this->search . '%')
                ->limit(50) // Limit results for performance
                ->get()
                ->toArray();

            $this->showDropdown = true;
        } else {
            $this->reagents = [];
            $this->showDropdown = false;
        }
    }

    // Method to select reagent
    public function selectReagent($reagentId)
    {
        $selectedReagent = Reagent::find($reagentId);

        if ($selectedReagent) {
            $this->reagent_id = $selectedReagent->id;
            $this->reagent_name = $selectedReagent->name;
            $this->search = $selectedReagent->name; // Update search field with selected name
            $this->catalog_no = $selectedReagent->catalog_no ?? '';
            $this->maker = $selectedReagent->vendor ?? '';
            $this->showDropdown = false;
            $this->reagents = []; // Clear the dropdown
        }
    }

    // Hide dropdown when clicking outside
    public function hideDropdown()
    {
        $this->showDropdown = false;
    }

    // Clear search and selection
    public function clearSearch()
    {
        $this->search = '';
        $this->reagent_name = '';
        $this->reagent_id = null;
        $this->catalog_no = '';
        $this->maker = '';
        $this->reagents = [];
        $this->showDropdown = false;
    }

    protected function rules()
    {
        return [
            'input_date'     => 'required|date',
            'po_no'          => 'nullable|string|max:50',
            'reagent_name'   => 'required|string|max:100',
            'maker'          => 'nullable|string|max:100',
            'catalog_no'     => 'nullable|string|max:100',
            'expired_date'   => 'required|date|after_or_equal:input_date',
            'initial_qty'    => 'required|numeric|min:0.01',
            'quantity_uom'   => 'required|string|in:pillow,g,mg,mL|max:20', // Added mL
            'site'           => 'nullable|string|max:100',
            'location'       => 'nullable|string|max:100',
            'lead_time'      => 'nullable|integer|min:0',
            'dept_owner_id'  => 'required|integer',
            'minimum_qty'    => 'nullable|numeric|min:0',
            'input_by'      => 'nullable|integer|exists:users,id',
        ];
    }

    protected function messages()
    {
        return [
            'reagent_name.required' => 'Reagent name is required.',
            'initial_qty.required' => 'Quantity is required.',
            'initial_qty.min' => 'Quantity must be greater than 0.',
            'quantity_uom.required' => 'Unit of measure is required.',
            'expired_date.required' => 'Expiry date is required.',
            'expired_date.after_or_equal' => 'Expiry date must be after input date.',
            'input_date.required' => 'Input date is required.',
            'dept_owner_id.required' => 'Department owner is required.',
        ];
    }

    // Fix Case 3: Real-time validation for minimum quantity
    public function updatedMinimumQty()
    {
        $this->validateOnly('minimum_qty');

        if ($this->minimum_qty && $this->initial_qty && $this->minimum_qty > $this->initial_qty) {
            $this->addError('minimum_qty', 'Minimum quantity cannot be greater than initial quantity.');

            // Dispatch browser event for real-time alert
            $this->dispatch('show-validation-error', [
                'message' => 'Minimum quantity cannot be greater than initial quantity!'
            ]);
        } else {
            $this->resetErrorBag('minimum_qty');
        }
    }

    // Fix Case 3: Also validate when initial_qty changes
    public function updatedInitialQty()
    {
        $this->validateOnly('initial_qty');

        if ($this->minimum_qty && $this->initial_qty && $this->minimum_qty > $this->initial_qty) {
            $this->addError('minimum_qty', 'Minimum quantity cannot be greater than initial quantity.');

            $this->dispatch('show-validation-error', [
                'message' => 'Minimum quantity cannot be greater than initial quantity!'
            ]);
        } else {
            $this->resetErrorBag('minimum_qty');
        }
    }

    public function confirmSaveStock()
    {
        // Validate first if you want to show errors before confirmation
        $this->validate();

        $this->dispatch('swal-confirm', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to save this stock?',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, save it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[\Livewire\Attributes\On('doSaveStock')]
    public function saveStock()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Additional validation check before saving
            if ($this->minimum_qty && $this->minimum_qty > $this->initial_qty) {
                $this->addError('minimum_qty', 'Minimum quantity cannot be greater than initial quantity.');

                // Fix Case 1: Dispatch SweetAlert for validation errors
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Validation Error!',
                    'text' => 'Minimum quantity cannot be greater than initial quantity.'
                ]);

                DB::rollBack();
                return;
            }

            $stock = new Stock();
            $stock->reagent_name = $this->reagent_name;
            $stock->po_no = $this->po_no;
            $stock->maker = $this->maker;
            $stock->catalog_no = $this->catalog_no;
            $stock->site = $this->site;
            $stock->location = $this->location;
            $stock->lead_time = $this->lead_time;
            $stock->initial_qty = $this->initial_qty;
            $stock->remaining_qty = $this->initial_qty;
            $stock->minimum_qty = $this->minimum_qty ?? 0;
            $stock->quantity_uom = $this->quantity_uom;
            $stock->expired_date = $this->expired_date;
            $stock->dept_owner_id = $this->dept_owner_id;
            $stock->input_by = $this->input_by;
            $stock->created_at = $this->input_date;
            $stock->save();

            DB::commit();

            $this->reset([
                'po_no',
                'reagent_name',
                'reagent_id',
                'maker',
                'catalog_no',
                'initial_qty',
                'quantity_uom',
                'site',
                'location',
                'lead_time',
                'minimum_qty',
                'expired_date',
                'search'
            ]);

            // Reset dates and owner
            $this->input_date = now()->format('Y-m-d');
            $this->dept_owner_id = Auth::user()->dept_id;
            $this->input_by = Auth::user()->id;
            $this->reagents = [];
            $this->showDropdown = false;

            // Enhanced SweetAlert for success
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Success!',
                'text' => 'Stock has been added successfully.',
                'timer' => 200,
                'showConfirmButton' => false
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Fix Case 1: SweetAlert for general errors
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => config('app.debug') ? $e->getMessage() : 'Failed to save stock. Please try again.'
            ]);
        }
    }

    // Method to handle form validation errors
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.create-stock');
    }
}

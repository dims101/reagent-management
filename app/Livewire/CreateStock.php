<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Stock;
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

    // Add property for owner name display
    public $owner_name;

    public function mount()
    {
        $this->input_date = now()->format('Y-m-d');
        $this->dept_owner_id = Auth::user()->dept_id ?? null;
        $this->owner_name = Auth::user()->department ? Auth::user()->department->name : 'Unknown Department';
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
            'quantity_uom'   => 'required|string|max:20',
            'site'           => 'nullable|string|max:100',
            'location'       => 'nullable|string|max:100',
            'lead_time'      => 'nullable|integer|min:0',
            'dept_owner_id'  => 'required|integer',
            'minimum_qty'    => 'nullable|numeric|min:0',
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
            $stock->created_at = $this->input_date;
            $stock->save();

            DB::commit();

            $this->reset([
                'po_no',
                'reagent_name',
                'maker',
                'catalog_no',
                'initial_qty',
                'quantity_uom',
                'site',
                'location',
                'lead_time',
                'minimum_qty',
                'expired_date'
            ]);

            // Reset dates and owner
            $this->input_date = now()->format('Y-m-d');
            $this->dept_owner_id = Auth::user()->dept_id;

            // Enhanced SweetAlert for success
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Success!',
                'text' => 'Stock has been added successfully.',
                'timer' => 200,
                'showConfirmButton' => false
            ]);

            // Also dispatch a general success event
            // $this->dispatch('stock-created-successfully');
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

    private function resetForm()
    {
        $this->reset([
            'po_no',
            'reagent_name',
            'maker',
            'catalog_no',
            'initial_qty',
            'quantity_uom',
            'site',
            'location',
            'lead_time',
            'minimum_qty'
        ]);

        $this->input_date = now()->format('Y-m-d');
        $this->expired_date = '';
        $this->dept_owner_id = Auth::user()->dept_id ?? null;
        $this->owner_name = Auth::user()->name ?? 'Unknown User';

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.create-stock');
    }
}

<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Ticket;
use App\Models\Reagent;
use App\Models\Customer;
use App\Models\Purpose;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateTicket extends Component
{
    #[Title('Create Reagent Ticket')]
    public $spk_no;
    public $quantity;
    public $uom;
    public $expected_finish_date;
    public $reagent_id;
    public $attachment;
    public $start_date;
    public $end_date;

    // Customer related properties
    public $customer_id;
    public $customer_search = '';
    public $customers = [];
    public $show_customer_dropdown = false;
    public $selected_customer_name = '';
    public $is_adding_new_customer = false;
    public $new_customer_name = '';

    // Purpose related properties
    public $purpose_id;
    public $purpose_search = '';
    public $purposes = [];
    public $show_purpose_dropdown = false;
    public $selected_purpose_name = '';
    public $is_adding_new_purpose = false;
    public $new_purpose_name = '';

    protected $listeners = [
        'saveTicket' => 'saveTicket',
        'customerSelected' => 'selectCustomer',
        'purposeSelected' => 'selectPurpose',
    ];

    protected $rules = [
        'spk_no' => 'required',
        'quantity' => 'required|numeric|min:1',
        'uom' => 'required|string',
        'expected_finish_date' => 'required|date',
        'reagent_id' => 'required',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'attachment' => 'nullable|string|max:255',
        'customer_id' => 'required',
        'purpose_id' => 'required',
        'new_customer_name' => 'required_if:is_adding_new_customer,true|string|max:255',
        'new_purpose_name' => 'required_if:is_adding_new_purpose,true|string|max:255',
    ];

    protected $messages = [
        'customer_id.required' => 'Please select or add a customer.',
        'purpose_id.required' => 'Please select or add a purpose.',
        'new_customer_name.required_if' => 'Customer name is required.',
        'new_purpose_name.required_if' => 'Purpose name is required.',
    ];

    public function updatedReagentId($value)
    {
        $this->reagent_id = $value['value'];
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customer_search) >= 1) {
            $this->searchCustomers();
            $this->show_customer_dropdown = true;
        } else {
            // Load initial customers if search is empty but dropdown should be shown
            if ($this->show_customer_dropdown) {
                $this->loadInitialCustomers();
            } else {
                $this->customers = [];
            }
        }

        // Reset selection if search changes
        if ($this->customer_search !== $this->selected_customer_name) {
            $this->customer_id = null;
            $this->selected_customer_name = '';
        }
    }

    public function updatedPurposeSearch()
    {
        if (strlen($this->purpose_search) >= 1) {
            $this->searchPurposes();
            $this->show_purpose_dropdown = true;
        } else {
            // Load initial purposes if search is empty but dropdown should be shown
            if ($this->show_purpose_dropdown) {
                $this->loadInitialPurposes();
            } else {
                $this->purposes = [];
            }
        }

        // Reset selection if search changes
        if ($this->purpose_search !== $this->selected_purpose_name) {
            $this->purpose_id = null;
            $this->selected_purpose_name = '';
        }
    }

    public function searchCustomers()
    {
        $this->customers = Customer::where('name', 'ILIKE', '%' . $this->customer_search . '%')
            ->limit(10)
            ->get();
    }

    public function searchPurposes()
    {
        $this->purposes = Purpose::where('name', 'ILIKE', '%' . $this->purpose_search . '%')
            ->where('type', 'ticket')
            ->limit(10)
            ->get();
    }

    // New method to load initial purposes
    public function loadInitialPurposes()
    {
        $this->purposes = Purpose::where('type', 'ticket')
            ->limit(5)
            ->get();
    }

    public function loadInitialCustomers()
    {
        $this->customers = Customer::orderBy('name')
            ->limit(5)
            ->get();
    }

    public function focusCustomer()
    {
        // Always load initial customers when focusing for the first time, like purpose
        if (empty($this->customer_search)) {
            $this->loadInitialCustomers();
        }
        $this->show_customer_dropdown = true;
    }

    // New method to handle purpose field focus
    public function focusPurpose()
    {
        if (empty($this->purpose_search)) {
            $this->loadInitialPurposes();
        }
        $this->show_purpose_dropdown = true;
    }

    public function selectCustomer($customerId, $customerName)
    {
        $this->customer_id = $customerId;
        $this->selected_customer_name = $customerName;
        $this->customer_search = $customerName;
        $this->show_customer_dropdown = false;
        $this->is_adding_new_customer = false;
        $this->resetErrorBag(['customer_id', 'new_customer_name']);
    }

    public function selectPurpose($purposeId, $purposeName)
    {
        $this->purpose_id = $purposeId;
        $this->selected_purpose_name = $purposeName;
        $this->purpose_search = $purposeName;
        $this->show_purpose_dropdown = false;
        $this->is_adding_new_purpose = false;
        $this->resetErrorBag(['purpose_id', 'new_purpose_name']);
    }

    public function showAddNewCustomer()
    {
        $this->is_adding_new_customer = true;
        $this->show_customer_dropdown = false;
        $this->customer_id = null;
        $this->selected_customer_name = '';
        $this->new_customer_name = $this->customer_search;
    }

    public function showAddNewPurpose()
    {
        $this->is_adding_new_purpose = true;
        $this->show_purpose_dropdown = false;
        $this->purpose_id = null;
        $this->selected_purpose_name = '';
        $this->new_purpose_name = $this->purpose_search;
    }

    public function cancelAddNewCustomer()
    {
        $this->is_adding_new_customer = false;
        $this->new_customer_name = '';
        $this->customer_search = '';
        $this->resetErrorBag(['new_customer_name']);
    }

    public function cancelAddNewPurpose()
    {
        $this->is_adding_new_purpose = false;
        $this->new_purpose_name = '';
        $this->purpose_search = '';
        $this->resetErrorBag(['new_purpose_name']);
    }

    public function addNewCustomer()
    {
        $this->validate([
            'new_customer_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Check if customer already exists
            $existingCustomer = Customer::where('name', 'ILIKE', trim($this->new_customer_name))->first();

            if ($existingCustomer) {
                $this->selectCustomer($existingCustomer->id, $existingCustomer->name);
            } else {
                $newCustomer = Customer::create([
                    'name' => trim($this->new_customer_name)
                ]);

                $this->selectCustomer($newCustomer->id, $newCustomer->name);
            }

            DB::commit();

            $this->is_adding_new_customer = false;
            $this->new_customer_name = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal-error', [
                'title' => 'Error!',
                'text' => 'Failed to add customer: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function addNewPurpose()
    {
        $this->validate([
            'new_purpose_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Check if purpose already exists
            $existingPurpose = Purpose::where('name', 'ILIKE', trim($this->new_purpose_name))
                ->where('type', 'ticket')
                ->first();

            if ($existingPurpose) {
                $this->selectPurpose($existingPurpose->id, $existingPurpose->name);
            } else {
                $newPurpose = Purpose::create([
                    'name' => trim($this->new_purpose_name),
                    'type' => 'ticket'
                ]);

                $this->selectPurpose($newPurpose->id, $newPurpose->name);
            }

            DB::commit();

            $this->is_adding_new_purpose = false;
            $this->new_purpose_name = '';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal-error', [
                'title' => 'Error!',
                'text' => 'Failed to add purpose: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function hideCustomerDropdown()
    {
        // Delay hiding to allow click events to register
        $this->dispatch('hide-dropdown-delayed');
    }

    // New method to handle purpose dropdown hiding
    public function hidePurposeDropdown()
    {
        // Delay hiding to allow click events to register
        $this->dispatch('hide-purpose-dropdown-delayed');
    }

    public function getLastSpkNo()
    {
        $lastSpkNo = Ticket::max('spk_no');
        $this->spk_no = $lastSpkNo ? $lastSpkNo + 1 : 1;
        return $lastSpkNo;
    }

    public function mount()
    {
        $lastSpkNo = Ticket::max('spk_no');
        $this->spk_no = $lastSpkNo ? $lastSpkNo + 1 : 1;
    }

    public function submit()
    {
        // Handle new customer creation before validation
        if ($this->is_adding_new_customer && $this->new_customer_name) {
            $this->addNewCustomer();
        }

        // Handle new purpose creation before validation
        if ($this->is_adding_new_purpose && $this->new_purpose_name) {
            $this->addNewPurpose();
        }

        $this->validate();

        // Fire SweetAlert confirmation
        $this->dispatch('swal', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to save this ticket?',
            'icon' => 'warning',
            'buttons' => [
                'cancel' => [
                    'text' => 'Cancel',
                    'visible' => true,
                    'className' => 'btn btn-secondary btn-pill',
                    'closeModal' => true,
                ],
                'confirm' => [
                    'text' => 'Yes, save it!',
                    'visible' => true,
                    'className' => 'btn btn-success btn-pill',
                    'closeModal' => true,
                ],
            ],
            'then' => 'saveTicket',
        ]);
    }

    public function saveTicket()
    {
        try {
            // Validate again before saving
            $this->validate();

            $ticket = Ticket::create([
                'spk_no' => $this->spk_no,
                'request_qty' => $this->quantity,
                'requested_by' => Auth::id(),
                'reagent_id' => $this->reagent_id,
                'purpose' => $this->selected_purpose_name, // Store purpose name for compatibility
                'purpose_id' => $this->purpose_id, // Store purpose_id for relationship
                'uom' => $this->uom,
                'expected_date' => $this->expected_finish_date,
                'customer_id' => $this->customer_id,
                'status' => 'open',
            ]);

            // Reset form fields
            $this->reset([
                'quantity',
                'uom',
                'expected_finish_date',
                'reagent_id',
                'attachment',
                'start_date',
                'end_date',
                'customer_id',
                'customer_search',
                'selected_customer_name',
                'is_adding_new_customer',
                'new_customer_name',
                'purpose_id',
                'purpose_search',
                'selected_purpose_name',
                'is_adding_new_purpose',
                'new_purpose_name'
            ]);
            $this->customers = [];
            $this->purposes = [];
            $this->show_customer_dropdown = false;
            $this->show_purpose_dropdown = false;
            $this->getLastSpkNo(); // Reset SPK number to the next available

            // Show success message
            $this->dispatch('swal-success', [
                'title' => 'Success!',
                'text' => 'Ticket has been created successfully.',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            // Show error message
            $this->dispatch('swal-error', [
                'title' => 'Error!',
                'text' => 'Failed to create ticket: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.create-ticket', [
            'reagents' => Reagent::select('name', 'id')
                ->where('type', 'Ticket')
                ->get(),
        ]);
    }
}

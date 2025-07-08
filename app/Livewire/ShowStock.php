<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Stock;
use App\Models\Request;
use App\Models\Purpose;
use Livewire\Component;
use App\Models\Approval;
use App\Models\Department;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ShowStock extends Component
{
    #[Title('Stock On Hand')]

    public $subTitle = "Manage stock on hand";
    public $request_no;
    public $purpose;
    public $reagent_id;
    public $request_qty;
    public $requested_by;
    public $approval_id;

    // Modal properties
    public $showModal = false;
    public $selectedStock;

    // Add these properties to your ShowStock class
    public $customer;
    public $customer_id;

    // Customer search properties
    public $customerSearch = '';
    public $selectedCustomer = null;
    public $customers = [];
    public $showCustomerDropdown = false;
    public $showAddNewCustomer = false;
    public $newCustomerName = '';

    // Purpose search properties
    public $purposeSearch = '';
    public $selectedPurpose = null;
    public $purposes = [];
    public $showPurposeDropdown = false;
    public $showAddNewPurpose = false;
    public $newPurposeName = '';


    // Add customer validation to your existing rules array
    protected $rules = [
        'request_no' => 'required|integer',
        'reagent_id' => 'required|integer|exists:stocks,id',
        'request_qty' => 'required|numeric|min:0.01',
        'purpose' => 'required|string|max:200',
        'customer' => 'required|string|max:200', // Add this line
        'requested_by' => 'required|integer|exists:users,id',
        'newPurposeName' => 'nullable|string|max:100|unique:purposes,name',
        'newCustomerName' => 'nullable|string|max:100|unique:customers,name', // Add this line
    ];

    // Add customer validation message to your existing messages array
    protected $messages = [
        'request_no.required' => 'Request number is required.',
        'reagent_id.required' => 'Reagent is required.',
        'request_qty.required' => 'Request quantity is required.',
        'purpose.required' => 'Purpose is required.',
        'customer.required' => 'Customer is required.', // Add this line
        'requested_by.required' => 'Requester is required.',
        'newPurposeName.unique' => 'This purpose already exists.',
        'newCustomerName.unique' => 'This customer already exists.', // Add this line
    ];

    // Add these methods to your ShowStock class

    public function loadInitialCustomers()
    {
        $this->customers = \App\Models\Customer::orderBy('name')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) >= 1) {
            $this->searchCustomers();
            $this->showCustomerDropdown = true;
        } else {
            $this->loadInitialCustomers();
            $this->showCustomerDropdown = true;
        }
        $this->selectedCustomer = null;
    }

    public function searchCustomers()
    {
        $this->customers = \App\Models\Customer::where('name', 'ilike', '%' . $this->customerSearch . '%')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectCustomer($customerId, $customerName)
    {
        $this->selectedCustomer = $customerId;
        $this->customerSearch = $customerName;
        $this->customer = $customerName;
        $this->customer_id = $customerId;
        $this->showCustomerDropdown = false;
        $this->showAddNewCustomer = false;
    }

    public function showAddNewCustomerForm()
    {
        $this->showAddNewCustomer = true;
        $this->newCustomerName = $this->customerSearch;
        $this->showCustomerDropdown = false;
    }

    public function addNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:100|unique:customers,name'
        ]);

        try {
            $newCustomer = \App\Models\Customer::create([
                'name' => $this->newCustomerName,
            ]);

            $this->selectCustomer($newCustomer->id, $newCustomer->name);
            $this->showAddNewCustomer = false;
            $this->newCustomerName = '';
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to add new customer: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelAddNewCustomer()
    {
        $this->showAddNewCustomer = false;
        $this->newCustomerName = '';
        $this->showCustomerDropdown = true;
    }

    public function focusCustomerField()
    {
        if (empty($this->customers)) {
            $this->loadInitialCustomers();
        }
        $this->showCustomerDropdown = true;
    }

    public function hideCustomerDropdown()
    {
        // Add a small delay to allow clicking on dropdown items
        $this->dispatch('hide-dropdown-delayed');
    }

    public function resetCustomerFields()
    {
        $this->customerSearch = '';
        $this->selectedCustomer = null;
        $this->customer_id = null;
        $this->customers = [];
        $this->showCustomerDropdown = false;
        $this->showAddNewCustomer = false;
        $this->newCustomerName = '';
    }

    public function openRequestModal($stockId)
    {
        $this->selectedStock = Stock::find($stockId);
        $this->reagent_id = $stockId;
        $this->showModal = true;
        $this->loadInitialPurposes();
        $this->loadInitialCustomers();
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedStock = null;
        $this->resetPurposeFields();
        $this->resetCustomerFields(); // Add this line
        $this->reset(['reagent_id', 'request_qty', 'purpose', 'customer']); // Add 'customer' to reset
        $this->dispatch('modal-closed');
    }

    public function resetPurposeFields()
    {
        $this->purposeSearch = '';
        $this->selectedPurpose = null;
        $this->purposes = [];
        $this->showPurposeDropdown = false;
        $this->showAddNewPurpose = false;
        $this->newPurposeName = '';
    }

    public function loadInitialPurposes()
    {
        $this->purposes = Purpose::where('type', 'stock')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function updatedPurposeSearch()
    {
        if (strlen($this->purposeSearch) >= 1) {
            $this->searchPurposes();
            $this->showPurposeDropdown = true;
        } else {
            $this->loadInitialPurposes();
            $this->showPurposeDropdown = true;
        }
        $this->selectedPurpose = null;
    }

    public function searchPurposes()
    {
        $this->purposes = Purpose::where('type', 'stock')
            ->where('name', 'ilike', '%' . $this->purposeSearch . '%')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectPurpose($purposeId, $purposeName)
    {
        $this->selectedPurpose = $purposeId;
        $this->purposeSearch = $purposeName;
        $this->purpose = $purposeName;
        $this->showPurposeDropdown = false;
        $this->showAddNewPurpose = false;
    }

    public function showAddNewPurposeForm()
    {
        $this->showAddNewPurpose = true;
        $this->newPurposeName = $this->purposeSearch;
        $this->showPurposeDropdown = false;
    }

    public function addNewPurpose()
    {
        $this->validate([
            'newPurposeName' => 'required|string|max:100|unique:purposes,name'
        ]);

        try {
            $newPurpose = Purpose::create([
                'name' => $this->newPurposeName,
                'type' => 'stock'
            ]);

            $this->selectPurpose($newPurpose->id, $newPurpose->name);
            $this->showAddNewPurpose = false;
            $this->newPurposeName = '';

            // $this->dispatch('swal', [
            //     'icon' => 'success',
            //     'title' => 'Success!',
            //     'text' => 'New purpose added successfully.'
            // ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to add new purpose: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelAddNewPurpose()
    {
        $this->showAddNewPurpose = false;
        $this->newPurposeName = '';
        $this->showPurposeDropdown = true;
    }

    public function focusPurposeField()
    {
        if (empty($this->purposes)) {
            $this->loadInitialPurposes();
        }
        $this->showPurposeDropdown = true;
    }

    public function hidePurposeDropdown()
    {
        // Add a small delay to allow clicking on dropdown items
        $this->dispatch('hide-dropdown-delayed');
    }

    public function confirmSubmitRequest()
    {
        $this->validate();

        $this->dispatch('swal-confirm', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to submit this stock request?',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, submit it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[\Livewire\Attributes\On('doSubmitRequest')]
    public function submitRequest()
    {
        $role_id = Auth::user()->role_id;
        if ($role_id == 2) {
            $status = 'approved';
        } elseif ($role_id == 3) {
            $status = 'waiting manager';
        } else {
            $status = 'pending';
        }
        try {
            $this->validate();

            // Check if requested quantity doesn't exceed available stock
            $stock = Stock::find($this->reagent_id);

            if (!$stock) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Selected reagent not found.'
                ]);
                return;
            }

            if ($this->request_qty > $stock->remaining_qty) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Request quantity cannot exceed available quantity.'
                ]);
                return;
            }
            try {
                // Get dept_id by joining requests and stocks where reagent_id = stock.id
                $deptId = Stock::where('id', $this->reagent_id)->value('dept_owner_id');
                $department = Department::find($deptId);
                $pic_id = $department ? $department->pic_id : null;
                $manager_id = $department ? $department->manager_id : null;

                $approval = Approval::create([
                    'dept_id'   => $deptId,
                    'assigned_pic_id' => $pic_id,
                    'assigned_manager_id' => $manager_id,
                ]);

                // $this->dispatch('swal', [
                //     'icon' => 'success',
                //     'title' => 'Request Submitted!',
                //     'text' => 'Reagent request submitted successfully.'
                // ]);
            } catch (\Exception $e) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to create approval: ' . $e->getMessage()
                ]);
                return;
            }

            try {

                Request::create([
                    'request_no'   => $this->request_no,
                    'reagent_id'   => $this->reagent_id,
                    'request_qty'  => $this->request_qty,
                    'purpose'      => $this->purpose,
                    'requested_by' => $this->requested_by,
                    'approval_id'  => $approval->id,
                    'customer_id'  => $this->customer_id,
                    'status'       => $status,
                ]);
                // Mail::to mail here
                $deptOwnerId = $stock->dept_owner_id;
                $mailManagerId = Department::find($deptOwnerId)->manager_id;
                $manager = User::find($mailManagerId);
                if ($role_id == 2) {
                    $approval->update([
                        'approval_reason' => "[System] : This request is created by Manager.",
                        'assigned_manager_date' => now(),
                    ]);
                    $stock->remaining_qty -= $this->request_qty;
                    $stock->save();
                    if ($stock->remaining_qty <= $stock->minimum_qty && $stock->remaining_qty <> 0) {
                        Mail::to($manager->email)->send(new \App\Mail\MinimumStock($manager->name, config('app.url') . '/stock/', $stock->reagent_name, $stock->remaining_qty));
                    }
                } elseif ($role_id == 3) {
                    $approval->update([
                        'approval_reason' => "[System] : This request is created by PIC.",
                        'assigned_pic_date' => now(),
                    ]);

                    Mail::to($manager->email)->send(new \App\Mail\SendApprovalManager($manager->name, config('app.url') . '/approval/'));
                } else {
                    $mailPicId = Department::find($deptOwnerId)->pic_id;
                    $pic = User::find($mailPicId);

                    Mail::to($pic->email)->send(new \App\Mail\SendApprovalPIC($pic->name, config('app.url') . '/approval/'));
                }
            } catch (\Exception $e) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to submit request: ' . $e->getMessage()
                ]);
            }

            // Reset fields and close modal
            $this->reset(['reagent_id', 'request_qty', 'purpose']);
            $this->resetPurposeFields();
            $this->closeModal();

            // Generate new request number for next request
            $lastRequestNo = Request::max('request_no');
            $this->request_no = $lastRequestNo ? $lastRequestNo + 1 : 1;

            // Dispatch success event
            $this->dispatch('request-submitted');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Validation Error!',
                'text' => collect($e->errors())->flatten()->first()
            ]);
        } catch (\Exception $e) {
            // Handle other errors
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to submit request: ' . $e->getMessage()
            ]);
        }
        $this->dispatch('approvalUpdated')->to(Sidebar::class);
    }

    #[\Livewire\Attributes\On('deleteStock')]
    public function deleteStock($id)
    {
        $stock = Stock::find($id);
        if ($stock) {
            $stock->delete();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Deleted!',
                'text' => 'Stock deleted successfully.'
            ]);
        } else {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Stock not found.'
            ]);
        }
    }

    public function mount()
    {
        $this->requested_by = Auth::user()->id;
        // Get the last request_no from the requests table and increment by 1
        $lastRequestNo = Request::max('request_no');
        $this->request_no = $lastRequestNo ? $lastRequestNo + 1 : 1;
    }

    public function render()
    {
        return view('livewire.show-stock', [
            'stocks' => Stock::with('department')
                ->where('dept_owner_id', Auth::user()->dept_id)
                ->orderBy('expired_date', 'asc')
                ->get()
        ]);
    }
}

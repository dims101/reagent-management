<?php

namespace App\Livewire;

use App\Models\Approval;
use App\Models\Department;
use App\Models\Purpose;
use App\Models\Request;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ShowStock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Stock On Hand')]
    public $subTitle = 'Manage stock on hand';

    public $request_no;

    public $purpose;

    public $reagent_id;

    public $request_qty;

    public $requested_by;

    public $approval_id;

    public $site;

    public $po_no;

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

    public $perPage = 10;

    public $search = '';

    public $reagentFilter = '';

    public $sortField = 'expired_date';

    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'reagentFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    // Add customer validation to your existing rules array
    protected $rules = [
        'request_no' => 'required|integer',
        'reagent_id' => 'required|integer|exists:stocks,id',
        'request_qty' => 'required|numeric|min:0.01',
        'purpose' => 'required|string|max:200',
        'customer' => 'required|string|max:200', // Add this line
        'requested_by' => 'required|integer|exists:users,id',
        'po_no' => 'nullable|string|max:200', // Add this line
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedReagentFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function reagentList()
    {
        return Stock::where('dept_owner_id', Auth::user()->dept_id)
            ->select('reagent_name')
            ->distinct()
            ->orderBy('reagent_name')
            ->pluck('reagent_name');
    }

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
        $this->customers = \App\Models\Customer::select('id', 'name')
            ->where('name', 'ilike', '%'.$this->customerSearch.'%')
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
        $this->customers = [];
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
            'newCustomerName' => 'required|string|max:100|unique:customers,name',
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
                'text' => 'Failed to add new customer: '.$e->getMessage(),
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
        // $this->loadInitialPurposes();
        // $this->loadInitialCustomers();
        $this->dispatch('modal-opened');
    }

    public function loadModalData()
    {
        if ($this->showModal && empty($this->purposes)) {
            $this->loadInitialPurposes();
        }
        if ($this->showModal && empty($this->customers)) {
            $this->loadInitialCustomers();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedStock = null;
        $this->resetPurposeFields();
        $this->resetCustomerFields(); // Add this line
        $this->reset(['reagent_id', 'request_qty', 'purpose', 'customer']); // Add 'customer' to reset

        $this->purposes = [];
        $this->customers = [];
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
        $this->purposes = Purpose::select('id', 'name')
            ->where('type', 'stock')
            ->where('name', 'ilike', '%'.$this->purposeSearch.'%')
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
        $this->purposes = [];
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
            'newPurposeName' => 'required|string|max:100|unique:purposes,name',
        ]);

        try {
            $newPurpose = Purpose::create([
                'name' => $this->newPurposeName,
                'type' => 'stock',
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
                'text' => 'Failed to add new purpose: '.$e->getMessage(),
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
        $this->showPurposeDropdown = true;
        if (empty($this->purposeSearch)) {
            $this->dispatch('load-purposes');
        }
    }

    #[On('load-purposes')]
    public function loadPurposesDeferred()
    {
        if (empty($this->purposes)) {
            $this->loadInitialPurposes();
        }
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
            'cancelButtonText' => 'Cancel',
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

            if (! $stock) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Selected reagent not found.',
                ]);

                return;
            }

            if ($this->request_qty > $stock->remaining_qty) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Request quantity cannot exceed available quantity.',
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
                    'dept_id' => $deptId,
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
                    'text' => 'Failed to create approval: '.$e->getMessage(),
                ]);

                return;
            }
            try {
                Request::create([
                    'request_no' => $this->request_no,
                    'reagent_id' => $this->reagent_id,
                    'request_qty' => $this->request_qty,
                    'purpose' => $this->purpose,
                    'requested_by' => $this->requested_by,
                    'approval_id' => $approval->id,
                    'customer_id' => $this->customer_id,
                    'status' => $status,
                ]);
                // Mail::to mail here
                $deptOwnerId = $stock->dept_owner_id;
                $mailManagerId = Department::find($deptOwnerId)->manager_id;
                $manager = User::find($mailManagerId);

                $picBandung = User::whereIn('id', [32])->get(); // 32
                $picSemarang = User::whereIn('id', [18])->get(); // 18
                $picGresik = User::whereIn('id', [30])->get(); // 30
                $picTangerang = User::whereIn('id', [37])->get(); // 37

                if ($role_id == 2) {
                    $approval->update([
                        'approval_reason' => '[System] : This request is created by Manager.',
                        'assigned_manager_date' => now(),
                    ]);
                    $stock->remaining_qty -= $this->request_qty;
                    $stock->save();
                    if ($stock->remaining_qty <= $stock->minimum_qty && $stock->remaining_qty != 0) {
                        Mail::to($manager->email)->send(new \App\Mail\MinimumStock($manager->name, config('app.url').'/stock/', $stock->reagent_name, $stock->remaining_qty));
                    }
                } elseif ($role_id == 3) {
                    $approval->update([
                        'approval_reason' => '[System] : This request is created by PIC.',
                        'assigned_pic_date' => now(),
                    ]);

                    Mail::to($manager->email)->send(new \App\Mail\SendApprovalManager($manager->name, config('app.url').'/approval/'));
                } else {

                    if (strtolower($stock->site) == 'tangerang') {
                        if ($picTangerang->isNotEmpty()) {
                            foreach ($picTangerang as $user) {
                                Mail::to($user->email)->send(
                                    new \App\Mail\SendPICApprovalPerSite($user->name, config('app.url').'/approval/', $stock->site)
                                );
                                Log::info("Email dikirim ke {$user->email}");
                            }
                        } else {
                            Log::warning('Tidak ditemukan user dengan ID 24 dan 7');
                        }
                    } elseif (strtolower($stock->site) == 'semarang') {
                        if ($picSemarang->isNotEmpty()) {
                            foreach ($picSemarang as $user) {
                                Mail::to($user->email)->send(
                                    new \App\Mail\SendPICApprovalPerSite($user->name, config('app.url').'/approval/', $stock->site)
                                );
                                Log::info("Email dikirim ke {$user->email}");
                            }
                        } else {
                            Log::warning('Tidak ditemukan user dengan ID 24 dan 7');
                        }
                    } elseif (strtolower($stock->site) == 'gresik') {
                        if ($picGresik->isNotEmpty()) {
                            foreach ($picGresik as $user) {
                                Mail::to($user->email)->send(
                                    new \App\Mail\SendPICApprovalPerSite($user->name, config('app.url').'/approval/', $stock->site)
                                );
                                Log::info("Email dikirim ke {$user->email}");
                            }
                        } else {
                            Log::warning('Tidak ditemukan user dengan ID 24 dan 7');
                        }
                    } elseif (strtolower($stock->site) == 'bandung') {
                        if ($picBandung->isNotEmpty()) {
                            foreach ($picBandung as $user) {
                                Mail::to($user->email)->send(
                                    new \App\Mail\SendPICApprovalPerSite($user->name, config('app.url').'/approval/', $stock->site)
                                );
                                Log::info("Email dikirim ke {$user->email}");
                            }
                        } else {
                            Log::warning('Tidak ditemukan user dengan ID 24 dan 7');
                        }
                    }

                    // $mailPicId = Department::find($deptOwnerId)->pic_id;
                    // $pic = User::find($mailPicId);
                    // Mail::to($pic->email)->send(new \App\Mail\SendApprovalPIC($pic->name, config('app.url') . '/approval/'));
                }
            } catch (\Exception $e) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to submit request: '.$e->getMessage(),
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
                'text' => collect($e->errors())->flatten()->first(),
            ]);
        } catch (\Exception $e) {
            // Handle other errors
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to submit request: '.$e->getMessage(),
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
                'text' => 'Stock deleted successfully.',
            ]);
        } else {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Stock not found.',
            ]);
        }
    }

    #[Computed]
    public function stocks()
    {
        $query = Stock::with(['department' => function ($q) {
            $q->select('id', 'name'); // ✅ Hanya ambil kolom yang dibutuhkan
        }])
            ->select([
                'id',
                'reagent_name',
                'maker',
                'catalog_no',
                'remaining_qty',
                'quantity_uom',
                'expired_date',
                'dept_owner_id',
                'location',
                'site',
                'input_by',
                'po_no',
                'no_lot',
            ])
            ->where('dept_owner_id', Auth::user()->dept_id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reagent_name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('maker', 'ilike', '%'.$this->search.'%')
                    ->orWhere('catalog_no', 'ilike', '%'.$this->search.'%')
                    ->orWhere('location', 'ilike', '%'.$this->search.'%')
                    ->orWhere('site', 'ilike', '%'.$this->search.'%')
                    ->orWhere('po_no', 'ilike', '%'.$this->search.'%')
                    ->orWhere('no_lot', 'ilike', '%'.$this->search.'%');
            });
        }

        if ($this->reagentFilter) {
            $query->where('reagent_name', $this->reagentFilter);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
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
        return view('livewire.show-stock');
    }

    // public function render()
    // {
    //     // Ambil dept_id dari user login
    //     $deptString = Auth::user()->dept_id;

    //     // Ubah jadi array
    //     $deptIds = collect(explode(',', $deptString))
    //         ->map(fn($v) => trim($v))
    //         ->filter(fn($v) => $v !== '')
    //         ->toArray();

    //     return view('livewire.show-stock', [
    //         'stocks' => Stock::with('department')
    //             ->when(!empty($deptIds), function ($query) use ($deptIds) {
    //                 $query->whereIn('dept_owner_id', $deptIds);
    //             })
    //             ->orderBy('expired_date', 'asc')
    //             ->get()
    //     ]);
    // }
}

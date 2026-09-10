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
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ShowOtherStock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Stock of Others')]
    public $subTitle = 'Manage stock of other departments';

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

    public $purposeOptions = [];

    public $showPurposeDropdown = false;

    public $selectedPurposeId = null;

    public $isNewPurpose = false;

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

    protected $rules = [
        'request_no' => 'required|integer',
        'reagent_id' => 'required|integer|exists:stocks,id',
        'request_qty' => 'required|numeric|min:0.01',
        'purpose' => 'required|string|max:200',
        'requested_by' => 'required|integer|exists:users,id',
    ];

    protected $messages = [
        'request_no.required' => 'Request number is required.',
        'reagent_id.required' => 'Reagent is required.',
        'request_qty.required' => 'Request quantity is required.',
        'purpose.required' => 'Purpose is required.',
        'requested_by.required' => 'Requester is required.',
    ];

    // ✅ TAMBAHKAN INI - Methods untuk search, filter, dan sorting
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

    public function getReagentListProperty()
    {
        return Stock::where('dept_owner_id', '<>', Auth::user()->dept_id)
            ->select('reagent_name')
            ->distinct()
            ->orderBy('reagent_name')
            ->pluck('reagent_name');
    }

    public function updatedPurposeSearch()
    {
        $this->searchPurposes();
    }

    public function searchPurposes()
    {
        if (empty($this->purposeSearch)) {
            // Show initial 5 purposes when field is empty
            $this->purposeOptions = Purpose::where('type', 'stock')
                ->orderBy('name')
                ->limit(5)
                ->get()
                ->toArray();
        } else {
            // Search purposes based on input
            $this->purposeOptions = Purpose::where('type', 'stock')
                ->where('name', 'ILIKE', '%'.$this->purposeSearch.'%')
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->toArray();
        }

        $this->showPurposeDropdown = true;
        $this->isNewPurpose = false;
    }

    public function selectPurpose($purposeId, $purposeName)
    {
        $this->selectedPurposeId = $purposeId;
        $this->purpose = $purposeName;
        $this->purposeSearch = $purposeName;
        $this->showPurposeDropdown = false;
        $this->isNewPurpose = false;
    }

    public function addNewPurpose()
    {
        $this->isNewPurpose = true;
        $this->newPurposeName = $this->purposeSearch;
        $this->showPurposeDropdown = false;
    }

    public function saveNewPurpose()
    {
        $this->validate([
            'newPurposeName' => 'required|string|max:200|unique:purposes,name',
        ], [
            'newPurposeName.required' => 'Purpose name is required.',
            'newPurposeName.unique' => 'This purpose already exists.',
        ]);

        try {
            $newPurpose = Purpose::create([
                'name' => $this->newPurposeName,
                'type' => 'stock',
            ]);

            $this->selectedPurposeId = $newPurpose->id;
            $this->purpose = $newPurpose->name;
            $this->purposeSearch = $newPurpose->name;
            $this->isNewPurpose = false;
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

    public function cancelNewPurpose()
    {
        $this->isNewPurpose = false;
        $this->newPurposeName = '';
        $this->purposeSearch = '';
        $this->showPurposeDropdown = false;
    }

    public function hidePurposeDropdown()
    {
        // Add a small delay to allow click events to register
        $this->dispatch('hide-purpose-dropdown');
    }

    public function showPurposeOptions()
    {
        $this->searchPurposes();
    }

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
        $this->customers = \App\Models\Customer::where('name', 'ilike', '%'.$this->customerSearch.'%')
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

        // Load initial purposes
        $this->purposeOptions = Purpose::where('type', 'stock')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->toArray();
        $this->loadInitialCustomers();

        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedStock = null;
        $this->resetCustomerFields(); // Add this line
        $this->reset(['reagent_id', 'customer', 'request_qty', 'purpose', 'purposeSearch', 'purposeOptions', 'showPurposeDropdown', 'selectedPurposeId', 'isNewPurpose', 'newPurposeName']);
        $this->dispatch('modal-closed');
    }

    public function submitRequest()
    {
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
                    'customer_id' => $this->customer_id, // Add customer_id here
                    'status' => 'pending',
                ]);

                // Mail::to mail here
                $deptOwnerId = Stock::find($this->reagent_id)->dept_owner_id;
                $mailPicId = Department::find($deptOwnerId)->pic_id;
                $pic = User::find($mailPicId);

                // SEMUA PIC DARI BERBAGAI SITE
                $picBandung = User::whereIn('id', [32])->get(); // 32
                $picSemarang = User::whereIn('id', [18])->get(); // 18
                $picGresik = User::whereIn('id', [30])->get(); // 30
                $picTangerang = User::whereIn('id', [37])->get(); // 37

                Mail::to($pic->email)->send(new \App\Mail\SendApprovalPIC($pic->name, config('app.url').'/approval/'));

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
            } catch (\Exception $e) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to submit request: '.$e->getMessage(),
                ]);

                return;
            }

            // Reset fields and close modal
            $this->reset(['reagent_id', 'request_qty', 'purpose', 'purposeSearch', 'purposeOptions', 'showPurposeDropdown', 'selectedPurposeId', 'isNewPurpose', 'newPurposeName']);
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
    }

    public function mount()
    {
        $this->requested_by = Auth::user()->id;
        // Get the last request_no from the requests table and increment by 1
        $lastRequestNo = Request::max('request_no');
        $this->request_no = $lastRequestNo ? $lastRequestNo + 1 : 1;
    }

    // ✅ GANTI METHOD INI
    public function render()
    {
        $query = Stock::with('department')
            ->where('dept_owner_id', '<>', Auth::user()->dept_id);

        // Search
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

        // Filter by reagent
        if ($this->reagentFilter) {
            $query->where('reagent_name', $this->reagentFilter);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.show-other-stock', [
            'stocks' => $query->paginate($this->perPage),
            'reagentList' => $this->reagentList,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Approval;
use App\Models\Department;
use App\Models\Request;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Title('Approval List')]
    public $subTitle = 'List of approvals pending or completed';

    public $showDetailModal = false;

    public $showApprovalModal = false;

    public $selectedApproval = null;

    public $selectedRequest = null;

    public $rejectReason; // Changed from $reason

    public $approvalReason; // New property

    public $showApprovalReason = false;

    public $showRejectReason = false;

    public $search = '';

    public $sortField = 'request_no';

    public $sortDirection = 'desc';

    public $perPage = 10;

    public function openDetailModal($request_no)
    {
        // $deptId = Auth::user()->dept_id;

        $approval = \App\Models\Request::query()
            ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->join('users', 'requests.requested_by', '=', 'users.id')
            ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->leftJoin('customers', 'requests.customer_id', '=', 'customers.id')
            ->where('requests.request_no', $request_no)
            ->select([
                'requests.request_no',
                'requests.created_at as request_date',
                'requests.request_qty',
                'requests.purpose',
                'requests.requested_by',
                'requests.approval_id',
                'requests.status as approval_status',
                'users.name as requester_name',
                'stocks.remaining_qty',
                'stocks.quantity_uom',
                'stocks.reagent_name',
                'customers.name as customer_name',
            ])
            ->first();

        $this->selectedApproval = $approval ? $approval->toArray() : null;
        $this->showDetailModal = true;
    }

    // public function openApprovalModal($request_no)
    // {
    //     $deptId = Auth::user()->dept_id;

    //     $request = \App\Models\Request::query()
    //         ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
    //         ->join('users', 'requests.requested_by', '=', 'users.id')
    //         ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
    //         ->join('approval_dept', 'approvals.id', '=', 'approval_dept.approval_id')
    //         ->where('approvals.dept_id', $deptId)
    //         ->where('requests.request_no', $request_no)
    //         ->select([
    //             'requests.request_no',
    //             'requests.created_at as request_date',
    //             'requests.request_qty',
    //             'requests.purpose',
    //             'requests.requested_by',
    //             'requests.approval_id',
    //             'requests.status as approval_status',
    //             'users.name as requester_name',
    //             'stocks.remaining_qty',
    //             'stocks.quantity_uom',
    //             'stocks.reagent_name',
    //             'approvals.reject_reason',
    //             'approvals.approval_reason',
    //         ])
    //         ->first();
    //     $this->rejectReason = $request->reject_reason ?? '';
    //     $this->approvalReason = $request->approval_reason ?? '';

    //     $this->showApprovalReason = !empty($this->approvalReason);
    //     $this->showRejectReason = !empty($this->rejectReason);

    //     $this->selectedRequest = $request ? $request->toArray() : null;
    //     $this->showApprovalModal = true;
    // }

    public function openApprovalModal($request_no)
    {
        $authId = Auth::id();

        // Ambil dept_id langsung dari model, fallback ke DB jika null
        $deptId = Auth::user()?->dept_id ?? DB::table('users')->where('id', $authId)->value('dept_id');

        // Validasi dept_id (diizinkan jika user memiliki dept_id atau merupakan Super User / Admin)
        if ((empty($deptId) || ! is_numeric($deptId)) && Auth::user()?->role_id != 1) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Unauthorized',
                'text' => 'You have no department assigned.',
            ]);

            return;
        }

        // Query data request
        $request = \App\Models\Request::query()
            ->leftJoin('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->leftJoin('users', 'requests.requested_by', '=', 'users.id')
            ->leftJoin('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->leftJoin('customers', 'requests.customer_id', '=', 'customers.id')
            ->where('requests.request_no', $request_no)
            ->select([
                'requests.request_no',
                'requests.created_at as request_date',
                'requests.request_qty',
                'requests.purpose',
                'requests.requested_by',
                'requests.approval_id',
                'requests.status as approval_status',
                'users.name as requester_name',
                'stocks.remaining_qty',
                'stocks.quantity_uom',
                'stocks.reagent_name',
                'approvals.reject_reason',
                'approvals.approval_reason',
                'customers.name as customer_name',
            ])
            ->first();

        if (! $request) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Not Found',
                'text' => 'No approval found for your departments.',
            ]);

            return;
        }

        // Set Livewire properties
        $this->rejectReason = $request->reject_reason ?? '';
        $this->approvalReason = $request->approval_reason ?? '';
        $this->showApprovalReason = false;
        $this->showRejectReason = ! empty($this->rejectReason);
        $this->selectedRequest = $request->toArray();
        $this->showApprovalModal = true;
    }

    // public function approveRequest()
    // {
    //     // if (!$this->showApprovalReason) {
    //     //     $this->showApprovalReason = true;
    //     //     $this->showRejectReason = false;
    //     //     return;
    //     // }

    //     // $this->validate([
    //     //     'approvalReason' => 'required|string|max:500'
    //     // ]);

    //     if (!$this->selectedRequest) {
    //         $this->dispatch('swal', [
    //             'icon' => 'error',
    //             'title' => 'Error!',
    //             'text' => 'No request selected.'
    //         ]);
    //         return;
    //     }
    //     if (empty(trim($this->approvalReason))) {
    //         $this->dispatch('swal', [
    //             'icon' => 'error',
    //             'title' => 'Error!',
    //             'text' => 'Reason is required for approval.'
    //         ]);
    //         return;
    //     }

    //     // Show confirmation dialog
    //     $this->dispatch('confirm-approve', [
    //         'request_no' => $this->selectedRequest['request_no']
    //     ]);
    // }

    public function approveRequest()
    {
        if (! $this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.',
            ]);

            return;
        }

        // Langsung tampilkan konfirmasi tanpa butuh reason
        $this->dispatch('confirm-approve', [
            'request_no' => $this->selectedRequest['request_no'],
        ]);
    }

    public function rejectRequest()
    {
        if (! $this->showRejectReason) {
            $this->showRejectReason = true;
            $this->showApprovalReason = false;

            return;
        }
        $this->validate([
            'rejectReason' => 'required|string|min:3|max:500',
        ]);

        if (! $this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.',
            ]);

            return;
        }

        if (empty(trim($this->rejectReason))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Reason is required for rejection.',
            ]);

            return;
        }

        // Show confirmation dialog
        $this->dispatch('confirm-reject', [
            'request_no' => $this->selectedRequest['request_no'],
            'reject_reason' => $this->rejectReason,
        ]);
    }

    // public function confirmApprove($request_no)
    // {
    //     try {
    //         $request = Request::where('request_no', $request_no)
    //             ->with('requester')
    //             ->first();

    //         if ($request) {

    //             if (Auth::user()->role_id == 3) {
    //                 $request->update(['status' => 'waiting manager']);
    //             } elseif (Auth::user()->role_id == 2) {
    //                 $request->update(['status' => 'approved']);
    //             } else {
    //                 $this->dispatch('swal', [
    //                     'icon' => 'error',
    //                     'title' => 'Unauthorized!',
    //                     'text' => 'You don\'t have authorization to approve this request.'
    //                 ]);
    //                 return;
    //             }

    //             // Update approval record
    //             $approval = Approval::find($request->approval_id);
    //             $dept = Department::find(Auth::user()->dept_id);
    //             $manager = User::find($dept->manager_id);
    //             $managerAndPIC = User::whereIn('id', [22, 37])->get();
    //             Log::info('Mengambil data user dengan ID 22 dan 37', [
    //                 'data' => $managerAndPIC
    //             ]);

    //             if ($approval) {
    //                 if (Auth::user()->role_id == 3) {
    //                     $approval->update([
    //                         'approval_reason' => Auth::user()->name . ": " . $this->approvalReason ?: 'Approved',
    //                         'assigned_pic_date' => now(),
    //                     ]);
    //                     Mail::to($manager->email)->send(new \App\Mail\SendApprovalManager($manager->name, config('app.url') . '/approval/'));
    //                 } elseif (Auth::user()->role_id == 2) {
    //                     $approval->update([
    //                         'approval_reason' => $this->approvalReason ?: 'Approved by Manager',
    //                         'assigned_manager_date' => now(),
    //                     ]);

    //                     $requestedStock = Stock::where('id', $request->reagent_id)
    //                         ->first();
    //                     $requestedStock->remaining_qty -= $request->request_qty;
    //                     $requestedStock->save();

    //                     if ($requestedStock->remaining_qty <= $requestedStock->minimum_qty && $requestedStock->remaining_qty <> 0) {
    //                         Mail::to($manager->email)->send(new \App\Mail\MinimumStock($manager->name, config('app.url') . '/stock/', $requestedStock->reagent_name, $requestedStock->remaining_qty));
    //                     }

    //                     if ($manager->dept_id != $request->requester->dept_id) {
    //                         Stock::create([
    //                             'reagent_name' => $requestedStock->reagent_name,
    //                             'po_no' => $requestedStock->po_no,
    //                             'maker' => $requestedStock->maker,
    //                             'catalog_no' => $requestedStock->catalog_no,
    //                             'site' => $requestedStock->site,
    //                             'lead_time' => $requestedStock->lead_time,
    //                             'initial_qty' => $request->request_qty,
    //                             'remaining_qty' => $request->request_qty,
    //                             'quantity_uom' => $requestedStock->quantity_uom,
    //                             'minimum_qty' => $requestedStock->minimum_qty,
    //                             'expired_date' => $requestedStock->expired_date,
    //                             'location' => $requestedStock->location,
    //                             'dept_owner_id' => $request->requester->dept_id,
    //                         ]);
    //                     }
    //                 }
    //             }

    //             $this->closeModal();
    //             $this->dispatch('swal', [
    //                 'icon' => 'success',
    //                 'title' => 'Success!',
    //                 'text' => 'Request has been approved successfully.'
    //             ]);
    //             $this->dispatch('approvalUpdated');
    //             $this->dispatch('approvalUpdated')->to(Sidebar::class);
    //         }
    //     } catch (\Exception $e) {
    //         $this->dispatch('swal', [
    //             'icon' => 'error',
    //             'title' => 'Error!',
    //             'text' => 'Failed to approve request: ' . $e->getMessage()
    //         ]);
    //     }
    // }

    public function confirmApprove($request_no)
    {
        try {
            $request = Request::with(['requester', 'approval', 'reagent'])
                ->where('request_no', $request_no)
                ->first();

            if (! $request) {
                return $this->swalError('Request not found.');
            }

            $user = Auth::user();
            if (! $user) {
                Log::error('Auth user not found');

                return $this->swalError('User not found.');
            }

            $isPic = ($user->role_id == 3);
            $isManager = ($user->role_id == 2 || $user->role_id == 1);

            if (! $isPic && ! $isManager) {
                return $this->swalError('You don\'t have authorization to approve this request.');
            }

            // update request status
            if ($user->role_id == 3) {
                $newStatus = 'waiting manager';
            } else {
                $newStatus = 'approved';
            }

            $request->update(['status' => $newStatus]);

            $approval = $request->approval;

            // DEPT_ID ASSUMED INTEGER (no normalization)
            $deptId = $user->dept_id ?? $request->requester?->dept_id;
            $dept = $deptId ? Department::find((int) $deptId) : null;

            // ambil manager jika ada
            $manager = null;
            if ($dept && ! empty($dept->manager_id)) {
                $manager = User::find((int) $dept->manager_id);
            }

            if ($approval) {
                if ($newStatus === 'waiting manager') { // PIC
                    $approval->update([
                        'approval_reason' => $this->approvalReason ?: ($user->name.': Approved'),
                        'assigned_pic_date' => now(),
                    ]);

                    if ($manager?->email) {
                        // production: consider ->queue() instead of ->send()
                        Mail::to($manager->email)->send(new \App\Mail\SendApprovalManager($manager->name, url('/approval')));
                    }
                } else { // Manager
                    $approval->update([
                        'approval_reason' => $this->approvalReason ?: 'Approved by Manager',
                        'assigned_manager_date' => now(),
                    ]);

                    // stock & copy logic
                    $this->handleStockUpdate($request, $manager);
                }
            }

            // UI feedback
            $this->closeModal();
            $this->swalSuccess('Request has been approved successfully.');
            $this->dispatch('approvalUpdated')->to(Sidebar::class);

        } catch (\Throwable $e) {
            Log::error('confirmApprove exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->swalError('Failed to approve request: '.$e->getMessage());
        }
    }

    /**
     * Update stock and handle cross-department stock creation
     */
    protected function handleStockUpdate($request, $manager)
    {
        $stock = $request->reagent;
        if (! $stock) {
            return;
        }

        $stock->remaining_qty = max(0, ($stock->remaining_qty ?? 0) - (int) $request->request_qty);
        $stock->save();

        if ($stock->remaining_qty <= ($stock->minimum_qty ?? 0) && $stock->remaining_qty != 0 && $manager?->email) {
            // production: consider queue
            Mail::to($manager->email)->send(new \App\Mail\MinimumStock(
                $manager->name,
                url('/stock'),
                $stock->reagent_name,
                $stock->remaining_qty
            ));
        }

        // compare dept ids directly (they are ints now)
        $reqDeptId = $request->requester?->dept_id;
        $managerDeptId = $manager?->dept_id;

        $reqDeptId = is_numeric($reqDeptId) ? (int) $reqDeptId : null;
        $managerDeptId = is_numeric($managerDeptId) ? (int) $managerDeptId : null;

        if ($reqDeptId && $managerDeptId && $reqDeptId !== $managerDeptId) {
            Stock::create([
                'reagent_name' => $stock->reagent_name,
                'po_no' => $stock->po_no,
                'maker' => $stock->maker,
                'catalog_no' => $stock->catalog_no,
                'site' => $stock->site,
                'lead_time' => $stock->lead_time,
                'initial_qty' => $request->request_qty,
                'remaining_qty' => $request->request_qty,
                'quantity_uom' => $stock->quantity_uom,
                'minimum_qty' => $stock->minimum_qty,
                'expired_date' => $stock->expired_date,
                'location' => $stock->location,
                'dept_owner_id' => $reqDeptId,
            ]);
        }
    }

    /** helper UI */
    protected function swalError($text)
    {
        $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error!', 'text' => $text]);
    }

    protected function swalSuccess($text)
    {
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Success!', 'text' => $text]);
    }

    public function confirmReject($request_no, $reject_reason = null)
    {
        try {
            // Use passed reason or fallback to component property
            $rejectReason = $reject_reason ?: $this->rejectReason;

            $request = Request::where('request_no', $request_no)->first();
            if ($request) {
                $request->update(['status' => 'rejected']);

                // Update approval record
                $approval = Approval::find($request->approval_id);
                if ($approval) {
                    if (Auth::user()->role_id == 3) {
                        $approval->update([
                            'reject_reason' => Auth::user()->name.': '.$rejectReason ?: 'Rejected',
                            'assigned_pic_date' => now(),
                        ]);
                    } elseif (Auth::user()->role_id == 2) {
                        // For manager, we can also set the reject reason
                        $approval->update([
                            'reject_reason' => Auth::user()->name.': '.$rejectReason ?: 'Rejected',
                            'assigned_manager_date' => now(),
                        ]);
                    }
                }

                $this->closeModal();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Request has been rejected successfully.',
                ]);
                $this->dispatch('approvalUpdated');
                $this->dispatch('approvalUpdated')->to(Sidebar::class);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to reject request: '.$e->getMessage(),
            ]);
        }
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showApprovalModal = false;
        $this->selectedApproval = null;
        $this->selectedRequest = null;
        $this->rejectReason = '';
        $this->approvalReason = '';
        $this->showApprovalReason = false; // Reset visibility
        $this->showRejectReason = false;
        $this->dispatch('modal-closed');
    }

    // public function render()
    // {
    //     $user = Auth::user();

    //     // PIC dari masing-masing site
    //     $picBandung = [32];
    //     $picSemarang = [18];
    //     $picGresik = [30];
    //     $picTangerang = [37];

    //     $approvalsQuery = \App\Models\Request::query()
    //         ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
    //         ->join('users', 'requests.requested_by', '=', 'users.id')
    //         ->join('departments as requester_dept', 'users.dept_id', '=', 'requester_dept.id')
    //         ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
    //         ->join('departments as owner_dept', 'stocks.dept_owner_id', '=', 'owner_dept.id')
    //         ->select([
    //             'requests.request_no',
    //             'requests.created_at as request_date',
    //             'requests.request_qty',
    //             'requests.purpose',
    //             'requests.requested_by',
    //             'requests.approval_id',
    //             'requests.status as approval_status',
    //             'users.name as requester_name',
    //             'stocks.remaining_qty',
    //             'stocks.site',
    //             'stocks.quantity_uom',
    //             'owner_dept.name as requested_to',
    //         ])
    //         ->orderBy('requests.request_no', 'desc');

    //     // Filtering berdasarkan role
    //     if ($user->role_id == 2) {
    //         // Role 2: bisa melihat semua data
    //         // Tidak perlu filter tambahan
    //     } elseif ($user->role_id == 3) {
    //         // Role 3: filter berdasarkan site sesuai user ID
    //         if (in_array($user->id, $picBandung)) {
    //             $approvalsQuery->where('stocks.site', 'Bandung');
    //         } elseif (in_array($user->id, $picSemarang)) {
    //             $approvalsQuery->where('stocks.site', 'Semarang');
    //         } elseif (in_array($user->id, $picGresik)) {
    //             $approvalsQuery->where('stocks.site', 'Gresik');
    //         } elseif (in_array($user->id, $picTangerang)) {
    //             $approvalsQuery->where('stocks.site', 'Tangerang');
    //         } else {
    //             // Jika user tidak cocok dengan site manapun, kembalikan kosong
    //             $approvalsQuery->whereRaw('1 = 0');
    //         }
    //     }

    //     $approvals = $approvalsQuery->get()->map(function ($request) {
    //         return [
    //             'approval_id'      => $request->approval_id,
    //             'status'           => $request->approval_status,
    //             'request_no'       => $request->request_no,
    //             'request_date'     => $request->request_date,
    //             'request_qty'      => $request->request_qty,
    //             'requester'        => $request->requester_name,
    //             'purpose'          => $request->purpose,
    //             'remaining_qty'    => $request->remaining_qty,
    //             'quantity_uom'     => $request->quantity_uom,
    //             'requested_to'     => $request->requested_to,
    //             'requester_id'     => $request->requested_by,
    //         ];
    //     });

    //     return view('livewire.approval-list')->with([
    //         'approvals' => $approvals
    //     ]);
    // }

    public function updatingSearch()
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

    public function render()
    {
        $user = Auth::user();

        // Role ID: 1 = Super User, 2 = Manager, 3 = PIC
        $allowedRoles = [1, 2, 3];
        if (! $user || ! in_array((int) $user->role_id, $allowedRoles, true)) {
            return view('livewire.approval-list')->with([
                'approvals' => collect(),
            ]);
        }

        // ✅ OPTIMASI: Query dengan pagination di database level (menggunakan leftJoin agar data tidak hilang jika ada relasi kosong)
        $query = \App\Models\Request::query()
            ->leftJoin('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->leftJoin('users', 'requests.requested_by', '=', 'users.id')
            ->leftJoin('departments as requester_dept', 'users.dept_id', '=', 'requester_dept.id')
            ->leftJoin('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->leftJoin('departments as owner_dept', 'stocks.dept_owner_id', '=', 'owner_dept.id')
            ->select([
                'requests.request_no',
                'requests.created_at as request_date',
                'requests.request_qty',
                'requests.purpose',
                'requests.requested_by',
                'requests.approval_id',
                'requests.status as approval_status',
                'users.name as requester_name',
                'users.dept_id as requester_dept',
                'stocks.remaining_qty',
                'stocks.site',
                'stocks.quantity_uom',
                'owner_dept.name as requested_to',
            ]);

        // Filter berdasarkan status sesuai role:
        // - Manager (role_id 2): hanya request berstatus 'waiting manager'
        // - PIC (role_id 3): hanya request berstatus 'pending'
        // - Super User (role_id 1): request berstatus 'pending' atau 'waiting manager'
        if ($user->role_id == 2) {
            $query->where('requests.status', 'waiting manager');
        } elseif ($user->role_id == 3) {
            $query->where('requests.status', 'pending');
        } elseif ($user->role_id == 1) {
            $query->whereIn('requests.status', ['pending', 'waiting manager']);
        }

        // Filter berdasarkan departemen jika user bukan Super User (role_id 1) dan memiliki dept_id
        if ($user->role_id != 1 && ! empty($user->dept_id)) {
            $query->where('stocks.dept_owner_id', $user->dept_id);
        }

        // ✅ GLOBAL SEARCH - cari di semua kolom yang tampil
        if (! empty($this->search)) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('requests.request_no', 'like', $searchTerm)
                    ->orWhere('users.name', 'like', $searchTerm)
                    ->orWhere('owner_dept.name', 'like', $searchTerm)
                    ->orWhere('requests.status', 'like', $searchTerm)
                    ->orWhereRaw("TO_CHAR(requests.created_at, 'DD-MM-YYYY') LIKE ?", [$searchTerm]);
            });
        }

        // ✅ SORTING
        $sortableColumns = [
            'request_no' => 'requests.request_no',
            'request_date' => 'requests.created_at',
            'requester' => 'users.name',
            'requested_to' => 'owner_dept.name',
            'status' => 'requests.status',
        ];

        if (array_key_exists($this->sortField, $sortableColumns)) {
            $query->orderBy($sortableColumns[$this->sortField], $this->sortDirection);
        } else {
            $query->orderBy('requests.request_no', 'desc');
        }

        // ✅ PAGINATION di database level (bukan collection)
        $paginatedRequests = $query->paginate($this->perPage);

        // ✅ Transform data setelah pagination
        $approvals = $paginatedRequests->through(function ($request) {
            return [
                'approval_id' => $request->approval_id,
                'status' => $request->approval_status,
                'request_no' => $request->request_no,
                'request_date' => $request->request_date,
                'request_qty' => $request->request_qty,
                'requester' => $request->requester_name,
                'purpose' => $request->purpose,
                'remaining_qty' => $request->remaining_qty,
                'quantity_uom' => $request->quantity_uom,
                'requested_to' => $request->requested_to,
                'requester_dept' => is_null($request->requester_dept) ? null : (int) $request->requester_dept,
                'requester_id' => $request->requested_by,
            ];
        });

        return view('livewire.approval-list')->with([
            'approvals' => $approvals,
        ]);
    }
}

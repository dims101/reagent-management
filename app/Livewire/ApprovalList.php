<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Request;
use Livewire\Component;
use App\Models\Approval;
use App\Models\Department;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Stock;


class ApprovalList extends Component
{
    #[Title('Approval List')]
    public $subTitle = "List of approvals pending or completed";
    public $showDetailModal = false;
    public $showApprovalModal = false;
    public $selectedApproval = null;
    public $selectedRequest = null;
    public $rejectReason; // Changed from $reason
    public $approvalReason; // New property
    public $showApprovalReason = false;
    public $showRejectReason = false;


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

    public function openApprovalModal($request_no)
    {
        $deptId = Auth::user()->dept_id;

        $request = \App\Models\Request::query()
            ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->join('users', 'requests.requested_by', '=', 'users.id')
            ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->where('approvals.dept_id', $deptId)
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
            ])
            ->first();
        $this->rejectReason = $request->reject_reason ?? '';
        $this->approvalReason = $request->approval_reason ?? '';

        $this->showApprovalReason = !empty($this->approvalReason);
        $this->showRejectReason = !empty($this->rejectReason);

        $this->selectedRequest = $request ? $request->toArray() : null;
        $this->showApprovalModal = true;
    }

    public function approveRequest()
    {
        if (!$this->showApprovalReason) {
            $this->showApprovalReason = true;
            $this->showRejectReason = false;
            return;
        }

        $this->validate([
            'approvalReason' => 'required|string|max:500'
        ]);

        if (!$this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.'
            ]);
            return;
        }
        if (empty(trim($this->approvalReason))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Reason is required for approval.'
            ]);
            return;
        }

        // Show confirmation dialog
        $this->dispatch('confirm-approve', [
            'request_no' => $this->selectedRequest['request_no']
        ]);
    }

    public function rejectRequest()
    {
        if (!$this->showRejectReason) {
            $this->showRejectReason = true;
            $this->showApprovalReason = false;
            return;
        }
        $this->validate([
            'rejectReason' => 'required|string|min:3|max:500'
        ]);

        if (!$this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.'
            ]);
            return;
        }

        if (empty(trim($this->rejectReason))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Reason is required for rejection.'
            ]);
            return;
        }

        // Show confirmation dialog
        $this->dispatch('confirm-reject', [
            'request_no' => $this->selectedRequest['request_no'],
            'reject_reason' => $this->rejectReason
        ]);
    }

    public function confirmApprove($request_no)
    {
        try {
            $request = Request::where('request_no', $request_no)
                ->with('requester')
                ->first();

            if ($request) {

                if (Auth::user()->role_id == 3) {
                    $request->update(['status' => 'waiting manager']);
                } elseif (Auth::user()->role_id == 2) {
                    $request->update(['status' => 'approved']);
                } else {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Unauthorized!',
                        'text' => 'You don\'t have authorization to approve this request.'
                    ]);
                    return;
                }

                // Update approval record
                $approval = Approval::find($request->approval_id);
                $dept = Department::find(Auth::user()->dept_id);
                $manager = User::find($dept->manager_id);

                if ($approval) {
                    if (Auth::user()->role_id == 3) {
                        $approval->update([
                            'approval_reason' => Auth::user()->name . ": " . $this->approvalReason ?: 'Approved',
                            'assigned_pic_date' => now(),
                        ]);
                        Mail::to($manager->email)->send(new \App\Mail\SendApprovalManager($manager->name, config('app.url') . '/approval/'));
                    } elseif (Auth::user()->role_id == 2) {
                        $approval->update([
                            'approval_reason' => $this->approvalReason ?: 'Approved by Manager',
                            'assigned_manager_date' => now(),
                        ]);

                        $requestedStock = Stock::where('id', $request->reagent_id)
                            ->first();
                        $requestedStock->remaining_qty -= $request->request_qty;
                        $requestedStock->save();

                        if ($requestedStock->remaining_qty <= $requestedStock->minimum_qty && $requestedStock->remaining_qty <> 0) {
                            Mail::to($manager->email)->send(new \App\Mail\MinimumStock($manager->name, config('app.url') . '/stock/', $requestedStock->reagent_name, $requestedStock->remaining_qty));
                        }

                        if ($manager->dept_id != $request->requester->dept_id) {
                            Stock::create([
                                'reagent_name' => $requestedStock->reagent_name,
                                'po_no' => $requestedStock->po_no,
                                'maker' => $requestedStock->maker,
                                'catalog_no' => $requestedStock->catalog_no,
                                'site' => $requestedStock->site,
                                'lead_time' => $requestedStock->lead_time,
                                'initial_qty' => $request->request_qty,
                                'remaining_qty' => $request->request_qty,
                                'quantity_uom' => $requestedStock->quantity_uom,
                                'minimum_qty' => $requestedStock->minimum_qty,
                                'expired_date' => $requestedStock->expired_date,
                                'location' => $requestedStock->location,
                                'dept_owner_id' => $request->requester->dept_id,
                            ]);
                        }
                    }
                }

                $this->closeModal();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Request has been approved successfully.'
                ]);
                $this->dispatch('approvalUpdated');
                $this->dispatch('approvalUpdated')->to(Sidebar::class);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to approve request: ' . $e->getMessage()
            ]);
        }
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
                            'reject_reason' => Auth::user()->name . ": " . $rejectReason ?: 'Rejected',
                            'assigned_pic_date' => now(),
                        ]);
                    } elseif (Auth::user()->role_id == 2) {
                        // For manager, we can also set the reject reason
                        $approval->update([
                            'reject_reason' => Auth::user()->name . ": " . $rejectReason ?: 'Rejected',
                            'assigned_manager_date' => now(),
                        ]);
                    }
                }

                $this->closeModal();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Request has been rejected successfully.'
                ]);
                $this->dispatch('approvalUpdated');
                $this->dispatch('approvalUpdated')->to(Sidebar::class);
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to reject request: ' . $e->getMessage()
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

    public function render()
    {
        // $deptId = Auth::user()->dept_id;

        $approvals = \App\Models\Request::query()
            ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->join('users', 'requests.requested_by', '=', 'users.id')
            ->join('departments as requester_dept', 'users.dept_id', '=', 'requester_dept.id')
            ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->join('departments as owner_dept', 'stocks.dept_owner_id', '=', 'owner_dept.id')
            // ->where('users.dept_id', Auth::user()->id)
            ->where('approvals.dept_id', Auth::user()->dept_id)
            ->orWhere('requester_dept.id', Auth::user()->dept_id)
            // ->whereIn('requests.status', ['pending', 'waiting manager'])
            ->orderBy('requests.request_no', 'desc')
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
                'owner_dept.name as requested_to',
            ])
            ->get()
            ->map(function ($request) {
                return [
                    'approval_id'      => $request->approval_id,
                    'status'           => $request->approval_status,
                    'request_no'       => $request->request_no,
                    'request_date'     => $request->request_date,
                    'request_qty'      => $request->request_qty,
                    'requester'        => $request->requester_name,
                    'purpose'          => $request->purpose,
                    'remaining_qty'    => $request->remaining_qty,
                    'quantity_uom'     => $request->quantity_uom,
                    'requested_to'     => $request->requested_to,
                    'requester_id'     => $request->requested_by,
                ];
            });

        return view('livewire.approval-list')->with([
            'approvals' => $approvals
        ]);
    }
}

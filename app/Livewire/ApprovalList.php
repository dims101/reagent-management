<?php

namespace App\Livewire;

use App\Models\Request;
use App\Models\Approval;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class ApprovalList extends Component
{
    #[Title('Approval List')]
    public $subTitle = "List of approvals pending or completed";
    public $showDetailModal = false;
    public $showApprovalModal = false;
    public $selectedApproval = null;
    public $selectedRequest = null;
    public $reason = '';

    public function openDetailModal($request_no)
    {
        $deptId = Auth::user()->dept_id;

        $approval = \App\Models\Request::query()
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
            ])
            ->first();

        $this->selectedRequest = $request ? $request->toArray() : null;
        $this->showApprovalModal = true;
        $this->reason = '';
    }

    public function approveRequest()
    {
        if (!$this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.'
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
        if (!$this->selectedRequest) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'No request selected.'
            ]);
            return;
        }

        if (empty(trim($this->reason))) {
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
            'reason' => $this->reason
        ]);
    }

    public function confirmApprove($request_no)
    {
        try {
            $request = Request::where('request_no', $request_no)->first();
            if ($request) {
                $request->update(['status' => 'approved']);

                // Update approval record
                $approval = Approval::find($request->approval_id);
                if ($approval) {
                    $approval->update([
                        'reason' => $this->reason ?: 'Approved', // Use reason if provided, otherwise default
                        'assigned_pic_date' => now(),
                    ]);
                }

                $this->closeModal();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Request has been approved successfully.'
                ]);
                $this->dispatch('approvalUpdated');
            }
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error!',
                'text' => 'Failed to approve request: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmReject($request_no, $reason = null)
    {
        try {
            // Use passed reason or fallback to component property
            $rejectionReason = $reason ?: $this->reason;

            $request = Request::where('request_no', $request_no)->first();
            if ($request) {
                $request->update(['status' => 'rejected']);

                // Update approval record
                $approval = Approval::find($request->approval_id);
                if ($approval) {
                    $approval->update([
                        'reason' => $rejectionReason,
                        'assigned_pic_date' => now(),
                    ]);
                }

                $this->closeModal();
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Success!',
                    'text' => 'Request has been rejected successfully.'
                ]);
                $this->dispatch('approvalUpdated');
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
        $this->reason = '';
    }

    public function render()
    {
        $deptId = Auth::user()->dept_id;

        $approvals = \App\Models\Request::query()
            ->join('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->join('users', 'requests.requested_by', '=', 'users.id')
            ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->where('approvals.dept_id', $deptId)
            ->orderBy('requests.created_at', 'asc')
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
                ];
            });

        return view('livewire.approval-list')->with([
            'approvals' => $approvals
        ]);
    }
}

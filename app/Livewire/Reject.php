<?php

namespace App\Livewire;

use App\Models\Request;
use Livewire\Component;
use Livewire\Attributes\Title;

class Reject extends Component
{
    #[Title('Reject List')]
    public $subTitle = 'List of Rejected Requests';
    public $detail_request_no = '';
    public $detail_request_date = '';
    public $detail_requester = '';
    public $detail_purpose = '';
    public $detail_request_qty = '';
    public $detail_quantity_uom = '';
    public $detail_reject_reason = '';

    protected $listeners = ['modal-open' => 'onModalOpen', 'modal-closed' => 'modalClosed'];

    public $selectedRequestNo;

    public function onModalOpen($request_no)
    {
        $this->selectedRequestNo = $request_no;
        // dd('code reached');
        $request = Request::join('approvals', 'requests.approval_id', '=', 'approvals.id')
            ->join('users as requester', 'requests.requested_by', '=', 'requester.id')
            ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
            ->where('requests.request_no', $request_no)
            ->first([
                'requests.request_no',
                'requests.created_at as request_date',
                'requester.name as requester',
                'requests.purpose',
                'requests.request_qty',
                'stocks.quantity_uom',
                'approvals.reject_reason as reject_reason',
                'approvals.approval_reason as approval_reason'
            ]);
        if ($request) {
            $this->detail_request_no = $request->request_no;
            $this->detail_request_date = $request->request_date;
            $this->detail_requester = $request->requester;
            $this->detail_purpose = $request->purpose;
            $this->detail_request_qty = $request->request_qty;
            $this->detail_quantity_uom = $request->quantity_uom;
            $this->detail_reject_reason = $request->reject_reason;
            // dd($request);
        } else {
            // Handle case where request is not found
            session()->flash('error', 'Request not found.');
        }
    }

    public function modalClosed()
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.reject', [
            'rejects' => Request::join('approvals', 'requests.approval_id', '=', 'approvals.id')
                ->join('users as requester', 'requests.requested_by', '=', 'requester.id')
                ->where('requests.status', 'rejected')
                ->orderBy('requests.request_no', 'desc')
                ->get([
                    'requests.request_no',
                    'requests.created_at as request_date',
                    'requester.name as requester'
                ])
        ]);
    }
}

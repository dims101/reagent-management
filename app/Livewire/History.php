<?php

namespace App\Livewire;

use App\Models\Request;
use Livewire\Component;
use Livewire\Attributes\Title;

class History extends Component
{
    #[Title('Approved List')]
    public $subTitle = 'List of Approved Requests';
    public $detail_request_no = '';
    public $detail_request_date = '';
    public $detail_requester = '';
    public $detail_purpose = '';
    public $detail_request_qty = '';
    public $detail_quantity_uom = '';
    public $detail_reason = '';

    protected $listeners = ['modal-open' => 'onModalOpen'];

    public $selectedRequestNo;

    public function onModalOpen($request_no)
    {
        $this->selectedRequestNo = $request_no;
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
                'approvals.reason as reason'
            ]);
        if ($request) {
            $this->detail_request_no = $request->request_no;
            $this->detail_request_date = $request->request_date;
            $this->detail_requester = $request->requester;
            $this->detail_purpose = $request->purpose;
            $this->detail_request_qty = $request->request_qty;
            $this->detail_quantity_uom = $request->quantity_uom;
            $this->detail_reason = $request->reason;
        } else {
            session()->flash('error', 'Request not found.');
        }
    }

    public function render()
    {
        return view('livewire.history', [
            'histories' => Request::join('approvals', 'requests.approval_id', '=', 'approvals.id')
                ->join('users as requester', 'requests.requested_by', '=', 'requester.id')
                ->where('requests.status', 'approved')
                ->get([
                    'requests.request_no',
                    'requests.created_at as request_date',
                    'requester.name as requester'
                ])
        ]);
    }
}

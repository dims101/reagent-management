<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Request;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class ShowStock extends Component
{
    #[Title('Stock On Hand')]

    public $subTitle = "Manage stock on hand";
    public $request_no;
    public $purpose;
    public $reagent_id;
    public $request_qty;
    public $request_by;
    public $approval_id;

    protected $rules = [
        'request_no' => 'required|integer',
        'reagent_id' => 'required|integer|exists:stocks,id',
        'request_qty' => 'required|numeric|min:0.01',
        'purpose' => 'nullable|string|max:200',
        'request_by' => 'required|integer|exists:users,id',
    ];

    public function submitRequest()
    {
        dd('code goes here');
        try {
            $validated = $this->validate();

            Request::create([
                'request_no'   => $this->request_no,
                'reagent_id'   => $this->reagent_id,
                'request_qty'  => $this->request_qty,
                'purpose'      => $this->purpose,
                'request_by'   => $this->request_by,
                'approval_id'  => $this->approval_id,
            ]);

            // Optionally, reset fields or emit events
            $this->reset(['reagent_id', 'request_qty', 'purpose']);
            session()->flash('success', 'Request submitted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        $this->request_by = Auth::user()->id;
        // Get the last request_no from the requests table and increment by 1
        $lastRequestNo = Request::max('request_no');
        $this->request_no = $lastRequestNo ? $lastRequestNo + 1 : 1;
        $lastApprovalId = Request::max('approval_id');
        $this->approval_id = $lastApprovalId ? $lastApprovalId + 1 : 1;
    }


    public function render()
    {
        return view('livewire.show-stock', [
            'stocks' => Stock::with('department')
                ->where('dept_owner_id', Auth::user()->dept_id)
                ->get()
        ]);
    }
}

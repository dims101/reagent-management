<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Stock;
use App\Models\Request;
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

    public function openRequestModal($stockId)
    {
        $this->selectedStock = Stock::find($stockId);
        $this->reagent_id = $stockId;
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedStock = null;
        $this->reset(['reagent_id', 'request_qty', 'purpose']);
        $this->dispatch('modal-closed');
    }

    public function submitRequest()
    {

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
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Request Submitted!',
                    'text' => 'Reagent request submitted successfully.'
                ]);
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
                    'status'       => 'pending',
                ]);
                // Mail::to mail here
                $deptOwnerId = Stock::find($this->reagent_id)->dept_owner_id;
                $mailPicId = Department::find($deptOwnerId)->pic_id;
                $pic = User::find($mailPicId);

                Mail::to($pic->email)->send(new \App\Mail\SendApprovalPIC($pic->name, config('app.url') . '/stock/'));
            } catch (\Exception $e) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to submit request: ' . $e->getMessage()
                ]);
            }

            // Reset fields and close modal
            $this->reset(['reagent_id', 'request_qty', 'purpose']);
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
                ->get()
        ]);
    }
}

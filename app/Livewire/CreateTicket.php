<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Ticket;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends Component
{
    #[Title('Create Reagent Ticket')]
    public $spk_no;
    public $quantity;
    public $uom;
    public $purpose;
    public $expected_finish_date;
    public $reagent_id;
    public $attachment;
    public $start_date;
    public $end_date;
    public $remaining_qty;
    protected $listeners = [
        'saveTicket' => 'saveTicket',
    ];

    protected $rules = [
        'spk_no' => 'required',
        'quantity' => 'required|numeric|min:1',
        'uom' => 'required|string',
        'purpose' => 'required|string',
        'expected_finish_date' => 'required|date',
        'reagent_id' => 'required|exists:stocks,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'attachment' => 'nullable|string|max:255',
    ];

    public function getLastSpkNo()
    {
        $lastSpkNo = Ticket::max('spk_no');
        $this->spk_no = $lastSpkNo ? $lastSpkNo + 1 : 1;
        return $lastSpkNo;
    }

    public function mount()
    {
        $lastSpkNo = Ticket::max('spk_no');
        $this->spk_no = $lastSpkNo ? $lastSpkNo + 1 : 1;
    }

    public function submit()
    {
        $this->validate();

        // Fire SweetAlert confirmation
        $this->dispatch('swal', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to save this ticket?',
            'icon' => 'warning',
            'buttons' => [
                'cancel' => [
                    'text' => 'Cancel',
                    'visible' => true,
                    'className' => 'btn btn-secondary btn-pill',
                    'closeModal' => true,
                ],
                'confirm' => [
                    'text' => 'Yes, save it!',
                    'visible' => true,
                    'className' => 'btn btn-success btn-pill',
                    'closeModal' => true,
                ],
            ],
            'then' => 'saveTicket', // Custom event for Livewire JS
        ]);
    }

    public function saveTicket()
    {
        // dd('Saving ticket...'); // Debugging line, remove in production
        Ticket::create([
            'spk_no' => $this->spk_no,
            'request_qty' => $this->quantity,
            'requested_by' => Auth::id(),
            'reagent_id' => $this->reagent_id,
            'purpose' => $this->purpose,
            'expected_date' => $this->expected_finish_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'attachment' => $this->attachment,
            'status' => 'open',
        ]);

        $this->reset(['quantity', 'uom', 'purpose', 'expected_finish_date', 'reagent_id', 'attachment', 'start_date', 'end_date']);
        $this->getLastSpkNo(); // Reset SPK number to the next available

        $this->dispatch('swal-success', [
            'title' => 'Success!',
            'text' => 'Ticket has been created.',
            'icon' => 'success',
        ]);
    }

    // Fixed method - using updatedReagentId instead of listener
    public function updatedReagentId()
    {
        if ($this->reagent_id) {
            $selectedStock = Stock::find($this->reagent_id);
            if ($selectedStock) {
                $this->uom = $selectedStock->quantity_uom ?? '';
                $this->remaining_qty = $selectedStock->remaining_qty ?? 0;
            } else {
                $this->uom = '';
            }
        } else {
            $this->uom = '';
            $this->remaining_qty = 0;
        }
    }

    public function render()
    {
        return view('livewire.create-ticket', [
            'reagents' => Stock::pluck('reagent_name', 'id'),
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Ticket;
use App\Models\Reagent;
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

    protected $listeners = [
        'saveTicket' => 'saveTicket',
    ];

    protected $rules = [
        'spk_no' => 'required',
        'quantity' => 'required|numeric|min:1',
        'uom' => 'required|string',
        'purpose' => 'required|string',
        'expected_finish_date' => 'required|date',
        'reagent_id' => 'required',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'attachment' => 'nullable|string|max:255',
    ];

    public function updatedReagentId($value)
    {
        $this->reagent_id = $value['value'];
    }

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
            'then' => 'saveTicket',
        ]);
    }

    public function saveTicket()
    {
        try {
            // Validate again before saving
            $this->validate();

            $ticket = Ticket::create([
                'spk_no' => $this->spk_no,
                'request_qty' => $this->quantity,
                'requested_by' => Auth::id(),
                'reagent_id' => $this->reagent_id,
                'purpose' => $this->purpose,
                'uom' => $this->uom,
                'expected_date' => $this->expected_finish_date,
                'status' => 'open',
            ]);

            // Reset form fields
            $this->reset(['quantity', 'uom', 'purpose', 'expected_finish_date', 'reagent_id', 'attachment', 'start_date', 'end_date']);
            $this->getLastSpkNo(); // Reset SPK number to the next available

            // Show success message
            $this->dispatch('swal-success', [
                'title' => 'Success!',
                'text' => 'Ticket has been created successfully.',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            // Show error message
            $this->dispatch('swal-error', [
                'title' => 'Error!',
                'text' => 'Failed to create ticket: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.create-ticket', [
            'reagents' => Reagent::select('name', 'id')
                ->where('type', 'Ticket')
                ->get(),
        ]);
    }
}

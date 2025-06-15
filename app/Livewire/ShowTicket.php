<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\Reagent;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class ShowTicket extends Component
{
    // Edit modal properties
    public $showEditModal = false;
    public $editTicketId;
    public $quantity;
    public $uom;
    public $reagent_id;
    public $expected_finish_date;
    public $purpose;

    // Server-side search properties
    public $reagentSearch = '';
    public $reagents = [];
    public $selectedReagentName = '';
    public $showReagentDropdown = false;

    // Delete confirmation
    public $selectedTicketId;

    public function mount()
    {
        // Load initial reagents (first 50)
        $this->loadReagents();
    }

    public function loadReagents($search = '')
    {
        $query = Reagent::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->where('type', 'Ticket');
        }

        $this->reagents = $query->orderBy('name')
            ->where('type', 'Ticket')
            ->limit(50)
            ->get();
    }

    public function updatedReagentSearch()
    {
        $this->loadReagents($this->reagentSearch);
        $this->showReagentDropdown = true;

        // Reset selection if search changes
        if ($this->reagentSearch !== $this->selectedReagentName) {
            $this->reagent_id = null;
            $this->selectedReagentName = '';
        }
    }

    public function selectReagent($reagentId, $reagentName)
    {
        $this->reagent_id = $reagentId;
        $this->selectedReagentName = $reagentName;
        $this->reagentSearch = $reagentName;
        $this->showReagentDropdown = false;
    }

    public function hideReagentDropdown()
    {
        // Small delay to allow click events to fire
        $this->dispatch('hide-dropdown-delayed');
    }

    #[On('hide-dropdown-delayed')]
    public function hideDropdownDelayed()
    {
        $this->showReagentDropdown = false;
    }

    public function editData($ticketId)
    {
        $ticket = Ticket::with('reagent')->findOrFail($ticketId);
        $this->editTicketId = $ticketId;
        $this->quantity = $ticket->request_qty;
        $this->uom = $ticket->uom ?? 'UoM';
        $this->reagent_id = $ticket->reagent_id;
        $this->expected_finish_date = $ticket->expected_date ? $ticket->expected_date->format('Y-m-d') : '';
        $this->purpose = $ticket->purpose;

        // Set selected reagent for search
        if ($ticket->reagent) {
            $this->selectedReagentName = $ticket->reagent->name;
            $this->reagentSearch = $ticket->reagent->name;
        } else {
            $this->selectedReagentName = '';
            $this->reagentSearch = '';
        }

        $this->showEditModal = true;
        $this->showReagentDropdown = false;

        // Load reagents that match current selection
        if ($this->reagentSearch) {
            $this->loadReagents($this->reagentSearch);
        } else {
            $this->loadReagents();
        }
    }

    public function updateTicket()
    {
        $this->validate([
            'quantity' => 'required|numeric|min:1',
            'reagent_id' => 'required|exists:reagents,id',
            'expected_finish_date' => 'required|date',
            'purpose' => 'required|string|max:500',
        ]);

        try {
            $ticket = Ticket::findOrFail($this->editTicketId);
            $ticket->update([
                'request_qty' => $this->quantity,
                'uom' => $this->uom,
                'reagent_id' => $this->reagent_id,
                'expected_date' => $this->expected_finish_date,
                'purpose' => $this->purpose,
            ]);

            $this->dispatch('swal', [
                'title' => 'Success!',
                'text' => 'Ticket updated successfully.',
                'icon' => 'success',
                'button' => 'OK'
            ]);

            $this->closeEditModal();
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to update ticket: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->showReagentDropdown = false;
        $this->reset([
            'editTicketId',
            'quantity',
            'uom',
            'reagent_id',
            'expected_finish_date',
            'purpose',
            'reagentSearch',
            'selectedReagentName'
        ]);

        // Reload initial reagents
        $this->loadReagents();
        $this->dispatch('modal-closed');
    }

    public function deleteData($ticketId)
    {
        $this->selectedTicketId = $ticketId;
        $this->dispatch('swal-confirm-delete', [
            'title' => 'Are you sure?',
            'text' => 'You won\'t be able to revert this!',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, delete it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[On('doDelete')]
    public function confirmDelete()
    {
        try {
            $ticket = Ticket::findOrFail($this->selectedTicketId);
            $ticket->delete();

            $this->dispatch('swal', [
                'title' => 'Deleted!',
                'text' => 'Ticket has been deleted.',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to delete ticket: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    #[Title('List of Reagent Tickets')]
    public function render()
    {
        $user = Auth::user();

        if ($user->dept_id == 1) {
            $tickets = Ticket::with(['reagent', 'requester.department'])
                ->orderBy('spk_no', 'desc')->get();
        } else {
            $tickets = Ticket::with(['reagent', 'requester.department'])
                ->whereHas('requester', function ($q) use ($user) {
                    $q->where('dept_id', $user->dept_id);
                })->orderBy('spk_no', 'desc')
                ->get();
        }

        return view('livewire.show-ticket')
            ->with([
                'tickets' => $tickets,
            ]);
    }
}

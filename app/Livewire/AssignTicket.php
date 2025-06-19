<?php

namespace App\Livewire;

use Log;
use App\Models\User;
use App\Models\Stock;
use App\Models\Ticket;
use App\Models\Reagent;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignTicket extends Component
{
    use WithFileUploads;

    #[Title('Assign Reagent Ticket')]
    public $tickets;
    public $showModal = false;
    public $showRejectReason = false;
    public $selectedTicketId;
    public $spk_no, $expected_date, $reagent_id, $attachment, $start_date;
    public $input_date, $expected_reason, $purpose, $assign_to, $deadline_date;
    public $reject_reason;

    protected $rules = [
        'spk_no' => 'required',
        'expected_date' => 'required|date',
        'reagent_id' => 'required|exists:reagents,id',
        'start_date' => 'required|date',
        'input_date' => 'required|date',
        'expected_reason' => 'required|string',
        'purpose' => 'required|string',
        'assign_to' => 'required|exists:users,id',
        'deadline_date' => 'required|date|after_or_equal:start_date',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ];

    protected $messages = [
        'deadline_date.after_or_equal' => 'Deadline date must be after or equal to start date.',
        'attachment.mimes' => 'Attachment must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
        'attachment.max' => 'Attachment may not be greater than 10MB.',
    ];

    public function mount()
    {
        $this->loadTickets();
    }

    public function loadTickets()
    {
        $this->tickets = Ticket::with(['reagent', 'requester', 'assignedTo'])
            // ->whereIn('status', ['pending', 'assigned'])
            ->when(auth()->user() && auth()->user()->role_id == 4, function ($query) {
                $query->where('assigned_to', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openModal($id)
    {
        $ticket = Ticket::with(['reagent', 'requester'])->findOrFail($id);
        // dd($ticket);
        $this->selectedTicketId = $id;
        $this->spk_no = $ticket->spk_no;
        $this->expected_date = $ticket->expected_date ? $ticket->expected_date->format('Y-m-d') : '';
        $this->reagent_id = $ticket->reagent_id;
        // If there is an attachment, set it to the existing attachment path; otherwise, set to null
        $this->attachment = $ticket->attachment ?? null;
        $this->start_date = $ticket->start_date ? $ticket->start_date->format('Y-m-d') : '';
        $this->input_date = $ticket->created_at ? $ticket->created_at->format('Y-m-d') : '';
        $this->expected_reason = $ticket->expected_reason;
        $this->purpose = $ticket->purpose;
        $this->assign_to = $ticket->assigned_to ?? '';
        $this->deadline_date = $ticket->end_date ? $ticket->end_date->format('Y-m-d') : '';
        $this->reject_reason = '';
        $this->showRejectReason = false;
        $this->showModal = true;

        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->reset([
            'showModal',
            'showRejectReason',
            'selectedTicketId',
            'spk_no',
            'expected_date',
            'reagent_id',
            'attachment',
            'start_date',
            'input_date',
            'expected_reason',
            'purpose',
            'assign_to',
            'deadline_date',
            'reject_reason'
        ]);
        $this->resetValidation();
        $this->dispatch('modal-closed');
    }

    public function removeAttachment()
    {
        // Show confirmation dialog before removing
        $this->dispatch('swal-confirm', [
            'title' => 'Remove Attachment?',
            'text' => 'Are you sure you want to remove this attachment?',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, remove it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[On('doRemoveAttachment')]
    public function doRemoveAttachment()
    {
        try {
            $ticket = Ticket::findOrFail($this->selectedTicketId);

            // Only delete if the attachment is a string (already saved in DB)
            if (is_string($ticket->attachment) && Storage::disk('public')->exists($ticket->attachment)) {
                Storage::disk('public')->delete($ticket->attachment);
            }

            $ticket->update(['attachment' => null]);
            $this->attachment = null;

            $this->dispatch('swal', [
                'title' => 'Removed!',
                'text' => 'Attachment has been removed.',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to remove attachment: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function showRejectInput()
    {
        $this->showRejectReason = true;
        $this->resetValidation();
    }

    public function assign()
    {
        $this->validate();

        try {
            $ticket = Ticket::findOrFail($this->selectedTicketId);

            $updateData = [
                'start_date' => $this->start_date,
                'end_date' => $this->deadline_date,
                'expected_reason' => $this->expected_reason,
                'assigned_to' => $this->assign_to,
                'status' => 'assigned',
            ];

            // Handle attachment upload with unique name
            if ($this->attachment) {
                // Generate a shorter unique filename
                $extension = $this->attachment->getClientOriginalExtension();
                $filename = 'att_' . uniqid() . '.' . $extension;

                // Store file
                $attachmentPath = $this->attachment->storeAs('attachments', $filename, 'public');
                $updateData['attachment'] = $attachmentPath;
            }

            $ticket->update($updateData);

            $this->closeModal();
            $this->loadTickets(); // Reload data
            $this->dispatch('swal', [
                'title' => 'Success!',
                'text' => 'Ticket assigned successfully.',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            // \Log::error('Assign ticket error: ' . $e->getMessage());

            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to assign ticket: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function confirmReject()
    {
        $this->validate([
            'reject_reason' => 'required|string|min:5|max:500'
        ]);

        $this->dispatch('swal-confirm', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to reject this ticket?',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, reject it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[On('doReject')]
    public function doReject()
    {
        try {
            $ticket = Ticket::findOrFail($this->selectedTicketId);

            // Update ticket with rejection reason
            $ticket->update([
                'status' => 'rejected',
                'reject_reason' => $this->reject_reason // Store the actual reject reason, not purpose
            ]);

            $this->dispatch('swal', [
                'title' => 'Rejected!',
                'text' => 'Ticket has been rejected.',
                'icon' => 'success'
            ]);

            $this->closeModal();
            $this->loadTickets();
        } catch (\Exception $e) {
            // Log::error('Reject ticket error: ' . $e->getMessage());

            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to reject ticket: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function closeTicket($ticketId)
    {
        $this->selectedTicketId = $ticketId;
        $this->dispatch('swal-confirm-close', [
            'title' => 'Are you sure?',
            'text' => 'Do you want to close this ticket?',
            'icon' => 'warning',
            'confirmButtonText' => 'Yes, close it!',
            'cancelButtonText' => 'Cancel'
        ]);
    }

    #[On('doClose')]
    public function confirmClose()
    {
        try {
            $ticket = Ticket::findOrFail($this->selectedTicketId);
            $ticket->update(['status' => 'closed']);

            $this->dispatch('swal', [
                'title' => 'Closed!',
                'text' => 'Ticket has been closed.',
                'icon' => 'success'
            ]);
            $this->closeModal();
            $this->loadTickets();
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error!',
                'text' => 'Failed to close ticket: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function render()
    {

        return view('livewire.assign-ticket', [
            'tickets' => $this->tickets,
            'reagents' => Reagent::where('type', 'Ticket')->pluck('name', 'id'),
            'users' => User::where('dept_id', '1')->where('role_id', 4)->pluck('name', 'id'),
        ]);
    }
}

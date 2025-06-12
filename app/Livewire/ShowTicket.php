<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class ShowTicket extends Component
{
    #[Title('List of Reagent Tickets')]
    public function render()
    {
        $user = Auth::user();

        if ($user->dept_id == 1) {
            // Super admin: show all tickets
            $tickets = Ticket::all();
        } else {
            // User: show only tickets from their department
            $tickets = Ticket::whereHas('requester', function ($q) use ($user) {
                $q->where('dept_id', $user->dept_id);
            })->get();
        }

        return view('livewire.show-ticket')
            ->with([
                'tickets' => $tickets,
            ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Request;
use Livewire\Component;
use Livewire\Attributes\Title;

class History extends Component
{
    #[Title('Approved List')]
    public $subTitle = 'List of Approved Requests';

    public function render()
    {
        return view('livewire.history', [
            'histories' => Request::join('approvals', 'requests.approval_id', '=', 'approvals.id')
                ->join('users as requester', 'requests.requested_by', '=', 'requester.id')
                ->join('stocks', 'requests.reagent_id', '=', 'stocks.id')
                ->join('departments', 'stocks.dept_owner_id', '=', 'departments.id')
                ->where('requests.status', 'approved')
                ->orderBy('approvals.assigned_manager_date', 'asc')
                ->get([
                    'approvals.assigned_manager_date as output_date',
                    'stocks.reagent_name',
                    'stocks.maker',
                    'stocks.catalog_no',
                    'requests.request_qty',
                    'stocks.quantity_uom',
                    'requests.purpose',
                    'requester.name as user',
                    'departments.name as dept_owner',
                ])
        ]);
    }
}

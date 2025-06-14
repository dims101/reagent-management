<?php

namespace App\Livewire;

use App\Models\Reagent;
use Livewire\Component;

class TestPage extends Component
{
    public $selectedReagent;
    public $selectedVendor;

    public function updatedSelectedReagent($value)
    {

        $this->selectedReagent = $value['value'];
    }

    public function render()
    {

        return view('livewire.test-page', [
            'reagents' => Reagent::select('id', 'name')->get()
        ]);
    }
}

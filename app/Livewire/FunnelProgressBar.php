<?php

namespace App\Livewire;

use Livewire\Component;

class FunnelProgressBar extends Component
{
    public $record;

    public function mount($record = null)
    {
        $this->record = $record;
    }

    public function render()
    {
        return view('livewire.funnel-progress-bar');
    }
}

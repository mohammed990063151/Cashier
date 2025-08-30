<?php
namespace App\Livewire;

use Livewire\Component;

class TestSelect extends Component
{
    public $filterCategory = 'all'; // القيمة الافتراضية

    public function render()
    {
        return view('livewire.test-select');
    }
}

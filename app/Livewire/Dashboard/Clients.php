<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class Clients extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap'; // لو بتستخدم bootstrap

    public $search = '';
    public $name, $phones = [''], $address;
    public $clientId;
    public $updateMode = false;

    protected $rules = [
        'name' => 'required|string',
        'phones' => 'required|array|min:1',
        'phones.0' => 'required',
        'address' => 'required|string',
    ];
protected $messages = [
    'name.required' => 'اسم العميل مطلوب.',
    'name.string' => 'اسم العميل يجب أن يكون نصًا.',
    'phones.required' => 'رقم الهاتف مطلوب.',
    'phones.array' => 'رقم الهاتف يجب أن يكون مصفوفة.',
    'phones.min' => 'يجب إدخال رقم هاتف واحد على الأقل.',
    'phones.0.required' => 'الرقم الأول للهاتف مطلوب.',
    'address.required' => 'العنوان مطلوب.',
    'address.string' => 'العنوان يجب أن يكون نصًا.',
];
    // إعادة التهيئة عند التغيير
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::when($this->search, function($q) {
                return $q->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('phone', 'like', '%' . $this->search . '%')
                         ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(5);

        return view('livewire.dashboard.clients', compact('clients'));
    }

    public function resetInput()
    {
        $this->name = '';
        $this->phones = [''];
        $this->address = '';
        $this->clientId = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        Client::create([
            'name' => $this->name,
            'phone' => array_filter($this->phones),
            'address' => $this->address,
        ]);

        session()->flash('success', __('site.added_successfully'));

        $this->resetInput();
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);

        $this->clientId = $client->id;
        $this->name = $client->name;
        $this->phones = is_array($client->phone) ? $client->phone : [$client->phone];
        $this->address = $client->address;
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        if ($this->clientId) {
            $client = Client::find($this->clientId);
            $client->update([
                'name' => $this->name,
                'phone' => array_filter($this->phones),
                'address' => $this->address,
            ]);
            session()->flash('success', __('site.updated_successfully'));
            $this->resetInput();
        }
    }

    public function destroy($id)
    {
        Client::find($id)->delete();
        session()->flash('success', __('site.deleted_successfully'));
    }
}

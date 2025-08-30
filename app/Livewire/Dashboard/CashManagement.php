<?php


namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\CashSetting;

class CashManagement extends Component
{
    use WithPagination;

    public $type;
    public $amount;
    public $description;
    public $transaction_date;
    public $category;
    public $filterCategory = 'all';
    public $settings;
    public $filterAmount;
public $filterDescription;
public $filterDate;
public $totalAmount = 0;




    public $categories = [
        'order' => 'فواتير المبيعات',
        'returns' => 'فواتير المرتجعات',
        'purchases' => 'فواتير المشتريات',
        'clients' => 'سندات العملاء',
        'suppliers' => 'سندات الموردين',
        'operational' => 'المصروفات',
        'direct' => 'إضافة/سحب نقد مباشر',
    ];

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $cash = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);

        $query = CashTransaction::query();

        // فلترة حسب الفئة
        if ($this->filterCategory !== 'all') {
            // dd($this->filterCategory);
            $query->where('category', $this->filterCategory);
        }

        if (!empty($this->filterAmount)) {
    $query->where('amount', 'like', '%' . $this->filterAmount . '%');
}

if (!empty($this->filterDescription)) {
    $query->where('description', 'like', '%' . $this->filterDescription . '%');
}

// فلترة حسب التاريخ
if (!empty($this->filterDate)) {
    $query->where('transaction_date', $this->filterDate);
}
        $transactions = $query->latest()->paginate(50);
        $this->totalAmount = $query->get()->sum(function($trx) {
    return $trx->type === 'add' ? $trx->amount : -$trx->amount;
});

        return view('livewire.dashboard.cash-management', [
            'cash' => $cash,
            'transactions' => $transactions,
        ]);
    }

    public function updatedFilterCategory()
    {
        $this->resetPage(); // إعادة الصفحة الأولى عند تغيير الفئة
    }
 protected $rules = [
        'type' => 'required|in:add,deduct',
        'amount' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'transaction_date' => 'required|date',
        'category' => 'nullable|string'
    ];


    public function mount()
    {
        $this->transaction_date = now()->format('Y-m-d');
        $this->settings = CashSetting::firstOrCreate(['id' => 1]);
    }



    public function storeTransaction()
    {
        $this->validate();

        $cash = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);

        if ($this->type === 'add') {
            $cash->balance += $this->amount;
        } else {
            if ($cash->balance < $this->amount) {
                session()->flash('error', 'الرصيد غير كافٍ.');
                return;
            }
            $cash->balance -= $this->amount;
        }
        $cash->save();

        CashTransaction::create([
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date,
            'category' => $this->category,
        ]);

        $this->resetInput();
        session()->flash('success', 'تمت العملية بنجاح');
    }

    public function resetInput()
    {
        $this->type = null;
        $this->amount = null;
        $this->description = null;
        $this->transaction_date = now()->format('Y-m-d');
        $this->category = null;
    }
    public function updatedFilterAmount()
{
    $this->resetPage();
}


public function updatedFilterDescription() { $this->resetPage(); }
public function updatedFilterDate() { $this->resetPage(); }

    // public function updatedFilterCategory()
    // {
    //     $this->resetPage(); // عند تغيير الفلتر إعادة الصفحة الأولى
    // }
}

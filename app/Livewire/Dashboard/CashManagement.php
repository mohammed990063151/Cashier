<?php


namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\CashSetting;
use App\Services\CashService;

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
public $totalDeducted = 0;
public $totalAdded = 0;





    public $categories = [
        'order' => 'دفع عند البيع',
        'payment' => 'دفعات العملاء',
        'returns' => 'مرتجعات الطلبات',
        'purchase' => 'فواتير المشتريات',
        'supplier_payment' => 'دفعات الموردين',
        'operational' => 'مصروفات تشغيلية',
        'other' => 'مصروفات أخرى',
        'direct' => 'إضافة/سحب نقد مباشر',
    ];

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $cash = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);

        $query = $this->filteredTransactionsQuery();

        $this->totalAdded = (float) (clone $query)->where('type', 'add')->sum('amount');
        $this->totalDeducted = (float) (clone $query)->where('type', 'deduct')->sum('amount');
        $this->totalAmount = $this->totalAdded - $this->totalDeducted;

        $transactions = (clone $query)
            ->with(['order.client', 'orderReturn.order'])
            ->latest()
            ->paginate(50);

        $totalReturnsOut = (float) (clone $query)->where('category', 'returns')->where('type', 'deduct')->sum('amount');

        return view('livewire.dashboard.cash-management', [
            'cash' => $cash,
            'transactions' => $transactions,
            'totalReturnsOut' => $totalReturnsOut,
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

        try {
            app(CashService::class)->record(
                $this->type,
                (float) $this->amount,
                $this->description,
                'direct',
                $this->transaction_date
            );
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->resetInput();
        session()->flash('success', 'تمت العملية بنجاح');
    }

    protected function filteredTransactionsQuery()
    {
        $query = CashTransaction::query();

        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        if (! empty($this->filterAmount)) {
            $query->where('amount', 'like', '%'.$this->filterAmount.'%');
        }

        if (! empty($this->filterDescription)) {
            $query->where('description', 'like', '%'.$this->filterDescription.'%');
        }

        if (! empty($this->filterDate)) {
            $query->where('transaction_date', $this->filterDate);
        }

        return $query;
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

<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use App\Models\Cash;
use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Overview extends Component
{
    public $startDate;
    public $endDate;
     public $clientsDueChart;

    public $categories_count;
    public $products_count;
    public $clients_count;
    public $users_count;

    public $salesOverview;
    public $profitsOverview;
    public $expensesOverview;
    public $purchasesOverview;
    public $clientsOverview;
    public $suppliersOverview;
    public $cashOverview;

    public $transactions;
    public $dates;
    public $dailyAdded;
    public $dailyDeducted;
    public $topProducts;

    public $filteredSales;
    public $filteredProfit;
    public $currentMonthSales;
    public $lastMonthSales;
    public $salesGrowth;

    // public $range = 'month'; // القيم: today, week, month
    public $selectedRange = 'month'; // القيم: today, week, month, custom
    public $salesChart;
    public $topProductsChart;
    public $dailyCashChart;
    public $profitChart;

    // تحديث عند تغيير النطاق الزمني
    public function updatedSelectedRange()
    {
        $this->selectedRange = $this->selectedRange;
        $this->loadData();
    }

    public function mount()
    {
        $this->setDatesByRange();
        $this->loadData();
    }

    public function updated($field)
    {
        if (in_array($field, ['startDate', 'endDate', 'selectedRange'])) {
            $this->loadData();
        }
    }


    private function setDatesByRange()
    {
        switch ($this->selectedRange) {
            case 'today':
                $this->startDate = Carbon::today()->toDateString();
                $this->endDate = Carbon::today()->toDateString();
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->toDateString();
                $this->endDate = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'month':
            default:
                $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                $this->endDate = Carbon::now()->endOfMonth()->toDateString();
                break;
        }
    }

    public function loadData()
    {

        if ($this->selectedRange !== 'custom') {
            $this->setDatesByRange();
        }


        // إحصائيات عامة
        $this->categories_count = Category::count();
        $this->products_count = Product::count();
        $this->clients_count = Client::count();
        $this->users_count = User::whereRoleIs('admin')->count();

        // مبيعات وأرباح
        $this->salesOverview = ['total_sales' => Order::sum('total_price')];
        $this->profitsOverview = ['total_profit' => Order::sum('profit')];

        // مصروفات ومشتريات
        $this->expensesOverview = ['total_expenses' => Expense::sum('amount')];
        $this->purchasesOverview = ['total_purchases' => DB::table('purchase_invoices')->sum('total')];

        // العملاء والموردين
        $this->clientsOverview = ['total_due' => Order::sum('remaining')];
        $this->suppliersOverview = ['total_due' => DB::table('suppliers')->sum('balance')];

        // الخزينة
        $cash = Cash::first();
        $this->cashOverview = ['balance' => $cash->balance ?? 0];

        // حركة الخزينة اليومية
        $dailyTransactions = CashTransaction::select(
            DB::raw('DATE(transaction_date) as day'),
            DB::raw('SUM(CASE WHEN type="add" THEN amount ELSE 0 END) as added'),
            DB::raw('SUM(CASE WHEN type="deduct" THEN amount ELSE 0 END) as deducted')
        )
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy(DB::raw('DATE(transaction_date)'))
            ->get();

        $this->dates = $dailyTransactions->pluck('day');
        $this->dailyAdded = $dailyTransactions->pluck('added');
        $this->dailyDeducted = $dailyTransactions->pluck('deducted');

        $this->transactions = CashTransaction::orderBy('transaction_date', 'desc')->get();

        // أفضل المنتجات
        $this->topProducts = Product::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get();

        // تصفية حسب التاريخ
        $ordersQuery = Order::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        $this->filteredSales = $ordersQuery->sum('total_price');
        $this->filteredProfit = $ordersQuery->sum('profit');

        // نمو المبيعات
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;
        $this->currentMonthSales = Order::whereMonth('created_at', $currentMonth)->sum('total_price');
        $this->lastMonthSales = Order::whereMonth('created_at', $lastMonth)->sum('total_price');
        $this->salesGrowth = $this->lastMonthSales > 0
            ? (($this->currentMonthSales - $this->lastMonthSales) / $this->lastMonthSales) * 100
            : 0;

        // ====== بيانات مبيعات يومية ======
        $salesByDay = Order::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('SUM(total_price) as total')
        )
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'day');

        $this->salesChart = [
            'labels' => $salesByDay->keys()->toArray(),
            'data' => $salesByDay->values()->toArray(),
        ];
// ====== المتبقي اليومي للعملاء ======
// ====== المتبقي اليومي للعملاء بدون فلتر التاريخ ======
$dueByDay = Order::select(
    DB::raw('DATE(created_at) as day'),
    DB::raw('SUM(remaining) as total_due')
)
->groupBy(DB::raw('DATE(created_at)'))
->orderBy(DB::raw('DATE(created_at)'))
->pluck('total_due', 'day');

$this->clientsDueChart = [
    'labels' => $dueByDay->keys()->toArray(),
    'data' => $dueByDay->values()->toArray(),
];


        // ====== الأرباح والمصروفات ======
        $this->profitChart = [
            'labels' => ['الأرباح', 'المصروفات'],
            'data' => [
                Order::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('profit'),
                Expense::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('amount'),
            ],
        ];

        // ====== أفضل المنتجات ======
        $this->topProductsChart = [
            'labels' => $this->topProducts->pluck('name')->toArray(),
            'data' => $this->topProducts->pluck('orders_count')->toArray(),
        ];

        // إعادة تحميل الرسوم البيانية عبر Livewire
        // إعادة تحميل الرسوم البيانية عبر Livewire
        if (method_exists($this, 'dispatchBrowserEvent')) {
            $this->dispatchBrowserEvent('refreshCharts', [
                'salesChart' => $this->salesChart,
                'profitChart' => $this->profitChart,
                'dailyCashChart' => [
                    'labels' => $this->dates->toArray(),
                    'added' => $this->dailyAdded->toArray(),
                    'deducted' => $this->dailyDeducted->toArray(),
                ],
                'topProductsChart' => $this->topProductsChart,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dashboard.overview');
    }
}

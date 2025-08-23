<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today'); // today, yesterday, last7days, this_month, last_month
        $reportsService = new ReportsService($filter);

        $salesOverview      = $reportsService->salesOverview();
        $profitsOverview    = $reportsService->profitsOverview();
        $clientsOverview    = $reportsService->clientsOverview();
        $suppliersOverview  = $reportsService->suppliersOverview();
        $purchasesOverview  = $reportsService->purchasesOverview();
        $expensesOverview   = $reportsService->expensesOverview();
        $cashOverview       = $reportsService->cashOverview();


          $categories_count = Category::count();
        $products_count = Product::count();
        $clients_count = Client::count();
        $users_count = User::whereRoleIs('admin')->count();

        $sales_data = Order::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_price) as sum')
        )->groupBy('month')->get();
        return view('dashboard.reports.overview', compact(
            'salesOverview', 'profitsOverview', 'clientsOverview', 'suppliersOverview',
            'purchasesOverview', 'expensesOverview', 'cashOverview','categories_count', 'products_count', 'clients_count', 'users_count', 'sales_data'
        ));
    }
}

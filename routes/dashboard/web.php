<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Dashboard Controllers
use App\Http\Controllers\Dashboard\WelcomeController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ClientController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\Dashboard\CashTransactionController;
use App\Http\Controllers\Dashboard\ExpenseController;
use App\Http\Controllers\Dashboard\PurchaseInvoiceController;
use App\Http\Controllers\Dashboard\SaleInvoiceController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\CashController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\Client\OrderController as ClientOrderController;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard.welcome');
});

// Auth routes
Auth::routes(['register' => false]);

// Home route (optional)
Route::get('/home', function () {
    return redirect()->route('dashboard.welcome');
})->name('home');

// Dashboard routes group
Route::middleware(['auth', 'web'])->prefix('dashboard')->name('dashboard.')->group(function () {

    // Dashboard welcome
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

    // Category routes
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Product routes
    Route::resource('products', ProductController::class)->except(['show']);

    // Client routes
    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('clients.orders', ClientOrderController::class)->except(['show']);

    // Order routes
    Route::resource('orders', OrderController::class);
    Route::get('orders/{order}/products', [OrderController::class, 'products'])->name('orders.products');

    // User routes
    Route::resource('users', UserController::class)->except(['show']);

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('profit-loss', [ReportController::class, 'profitLoss'])->name('profitLoss');
        Route::get('sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('profit', [ReportController::class, 'profitLoss'])->name('profit');
        Route::get('clients', [ReportController::class, 'clientsReport'])->name('clients');
    });

    // Cash Transactions
    Route::get('cash-transactions', [CashTransactionController::class, 'index'])->name('cash.transactions');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Purchase Invoices
    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
    Route::get('purchase-invoices/{purchaseInvoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoices.print');

    // Sale Invoices
    Route::resource('sale-invoices', SaleInvoiceController::class);
    Route::get('sale-invoices/{sale_invoice}/print', [SaleInvoiceController::class, 'print'])->name('sale-invoices.print');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Cash Controller
    Route::get('cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('cash/store', [CashController::class, 'storeTransaction'])->name('cash.store');
    Route::get('cash/settings', [CashController::class, 'settings'])->name('cash.settings');
    Route::post('cash/settings', [CashController::class, 'updateSettings'])->name('cash.update.settings');
    Route::get('cash/filter', [CashController::class, 'filterTransactions'])->name('cash.filter');

    // Dashboard Reports Controller
    Route::prefix('reports')->name('reports.')->group(function () {
        // Sales
        Route::get('sales/detail', [DashboardController::class, 'salesDetail'])->name('sales.detail');
        Route::get('sales/summary', [DashboardController::class, 'salesSummary'])->name('sales.summary');
        Route::get('sales/by_category', [DashboardController::class, 'salesByCategory'])->name('sales.by_category');
        Route::get('sales/unpaid_invoices', [DashboardController::class, 'salesUnpaidInvoices'])->name('sales.unpaid_invoices');
        Route::get('sales/all_invoices', [DashboardController::class, 'salesAllInvoices'])->name('sales.all_invoices');

        // Profits
        Route::get('profits/detail', [DashboardController::class, 'profitsDetail'])->name('profits.detail');
        Route::get('profits/summary', [DashboardController::class, 'profitsSummary'])->name('profits.summary');
        Route::get('profits/by_products', [DashboardController::class, 'profitsByProducts'])->name('profits.by_products');

        // Clients
        Route::get('clients/remaining', [DashboardController::class, 'clientsRemaining'])->name('clients.remaining');
        Route::get('clients/invoices', [DashboardController::class, 'clientsInvoices'])->name('clients.invoices');
        Route::get('clients/products', [DashboardController::class, 'clientsProducts'])->name('clients.products');
        Route::get('clients/statement', [DashboardController::class, 'clientsStatement'])->name('clients.statement');

        // Suppliers
        Route::get('suppliers/remaining', [DashboardController::class, 'suppliersRemaining'])->name('suppliers.remaining');
        Route::get('suppliers/invoices', [DashboardController::class, 'suppliersInvoices'])->name('suppliers.invoices');
        Route::get('suppliers/products', [DashboardController::class, 'suppliersProducts'])->name('suppliers.products');
        Route::get('suppliers/statement', [DashboardController::class, 'suppliersStatement'])->name('suppliers.statement');

        // Purchases
        Route::get('purchases/detail', [DashboardController::class, 'purchasesDetail'])->name('purchases.detail');
        Route::get('purchases/summary', [DashboardController::class, 'purchasesSummary'])->name('purchases.summary');
        Route::get('purchases/by_category', [DashboardController::class, 'purchasesByCategory'])->name('purchases.by_category');
        Route::get('purchases/unpaid_invoices', [DashboardController::class, 'purchasesUnpaidInvoices'])->name('purchases.unpaid_invoices');
        Route::get('purchases/all_invoices', [DashboardController::class, 'purchasesAllInvoices'])->name('purchases.all_invoices');

        // Stock
        Route::get('stock/detail', [DashboardController::class, 'stockDetail'])->name('stock.detail');
        Route::get('stock/summary', [DashboardController::class, 'stockSummary'])->name('stock.summary');
        Route::get('stock/price_changes', [DashboardController::class, 'stockPriceChanges'])->name('stock.price_changes');

        // Expenses
        Route::get('expenses/detail', [DashboardController::class, 'expensesDetail'])->name('expenses.detail');
        Route::get('expenses/summary', [DashboardController::class, 'expensesSummary'])->name('expenses.summary');

        // Cash
        Route::get('cash', [DashboardController::class, 'cashFlow'])->name('cash');
    });

});

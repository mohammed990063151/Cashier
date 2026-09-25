<?php

namespace App\Services\AiAssistant;

use App\Models\Category;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Support\DecimalMath;
use App\Support\SaleUnits;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssistantTools
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function searchProducts(?string $query = null, int $limit = 12): Collection
    {
        return Product::query()
            ->with('category')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%');
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $p) => $this->productCard($p));
    }

    public function findProduct(int $id): ?array
    {
        $product = Product::with('category')->find($id);

        return $product ? $this->productCard($product) : null;
    }

    public function productExists(string $name): array
    {
        $matches = Product::query()
            ->where('name', 'like', '%'.$name.'%')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return [
            'exists' => $matches->isNotEmpty(),
            'count' => $matches->count(),
            'products' => $matches->map(fn (Product $p) => $this->productCard($p))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productCard(Product $product): array
    {
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $stock = DecimalMath::round($product->stock);
        $sale = DecimalMath::round($product->sale_price);
        $purchase = DecimalMath::round($product->purchase_price);

        $cartons = $measure === SaleUnits::UNIT_KILO
            ? null
            : ($bulk > 0 ? DecimalMath::div($stock, $bulk) : 0);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->name ?? '—',
            'measure_unit' => $measure,
            'measure_label' => SaleUnits::measureUnitLabel($measure),
            'sale_mode' => SaleUnits::normalizeSaleMode($product->sale_mode ?? null),
            'pieces_per_carton' => $bulk,
            'stock' => $stock,
            'stock_display' => app(\App\Services\ProductService::class)->stockDisplay($product),
            'sale_price' => $sale,
            'purchase_price' => $purchase,
            'carton_stock' => $cartons,
            'stock_value_purchase' => DecimalMath::mul($stock, $purchase),
            'stock_value_sale' => DecimalMath::mul($stock, $sale),
            'image' => $product->image_path,
        ];
    }

    /**
     * احسب قيمة كمية معينة (حبة / نصف كرتونة / كرتونة).
     *
     * @return array<string, mixed>
     */
    public function calculateQuantityValue(Product $product, float $qty, string $unit = 'piece'): array
    {
        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $unit = $measure === SaleUnits::UNIT_KILO ? 'kilo' : $unit;

        $multiplier = match ($unit) {
            'carton', 'bulk' => (float) $bulk,
            'half_carton', 'half' => SaleUnits::halfCartonPieces($bulk),
            'kilo' => 1.0,
            default => 1.0,
        };

        $pieces = DecimalMath::mul($qty, $multiplier);
        $entry = SaleUnits::toEntryValues($product);

        if ($measure === SaleUnits::UNIT_KILO || $unit === 'kilo') {
            $saleUnitPrice = DecimalMath::round((float) $entry['sale_price']);
            $purchaseUnitPrice = DecimalMath::round((float) $entry['purchase_price']);
            $saleTotal = DecimalMath::round(DecimalMath::mul($qty, $saleUnitPrice));
            $purchaseTotal = DecimalMath::round(DecimalMath::mul($qty, $purchaseUnitPrice));
        } elseif ($measure === SaleUnits::UNIT_CARTON && in_array($unit, ['carton', 'bulk'], true)) {
            $saleUnitPrice = DecimalMath::money((float) $entry['sale_price']);
            $purchaseUnitPrice = DecimalMath::money((float) $entry['purchase_price']);
            $saleTotal = DecimalMath::money($qty * $saleUnitPrice);
            $purchaseTotal = DecimalMath::money($qty * $purchaseUnitPrice);
        } elseif ($measure === SaleUnits::UNIT_CARTON && in_array($unit, ['half_carton', 'half'], true)) {
            $saleUnitPrice = DecimalMath::money((float) $entry['sale_price'] / 2);
            $purchaseUnitPrice = DecimalMath::money((float) $entry['purchase_price'] / 2);
            $saleTotal = DecimalMath::money($qty * $saleUnitPrice);
            $purchaseTotal = DecimalMath::money($qty * $purchaseUnitPrice);
        } else {
            $saleUnitPrice = DecimalMath::money((float) $product->sale_price * $multiplier);
            $purchaseUnitPrice = DecimalMath::money((float) $product->purchase_price * $multiplier);
            $saleTotal = DecimalMath::money($qty * $saleUnitPrice);
            $purchaseTotal = DecimalMath::money($qty * $purchaseUnitPrice);
        }
        $stock = DecimalMath::round($product->stock);
        $available = $stock >= $pieces;

        $unitLabel = match ($unit) {
            'carton', 'bulk' => SaleUnits::bulkLabel($bulk),
            'half_carton', 'half' => SaleUnits::halfCartonLabel($bulk),
            'kilo' => 'كيلو',
            default => 'حبة',
        };

        return [
            'product' => $product->name,
            'qty' => DecimalMath::round($qty),
            'unit' => $unit,
            'unit_label' => $unitLabel,
            'pieces_equivalent' => $pieces,
            'sale_unit_price' => $saleUnitPrice,
            'purchase_unit_price' => $purchaseUnitPrice,
            'sale_total' => $saleTotal,
            'purchase_total' => $purchaseTotal,
            'current_stock' => $stock,
            'stock_display' => app(\App\Services\ProductService::class)->stockDisplay($product),
            'available' => $available,
            'shortage' => $available ? 0 : DecimalMath::sub($pieces, $stock),
        ];
    }

    public function inventorySummary(): array
    {
        $products = Product::query()->get();
        $totalSku = $products->count();
        $totalStock = DecimalMath::round($products->sum(fn ($p) => (float) $p->stock));
        $purchaseValue = DecimalMath::round($products->sum(fn ($p) => (float) $p->stock * (float) $p->purchase_price));
        $saleValue = DecimalMath::round($products->sum(fn ($p) => (float) $p->stock * (float) $p->sale_price));
        $lowStock = $products->filter(fn ($p) => (float) $p->stock > 0 && (float) $p->stock <= 10)->count();
        $outOfStock = $products->filter(fn ($p) => (float) $p->stock <= 0)->count();

        return compact('totalSku', 'totalStock', 'purchaseValue', 'saleValue', 'lowStock', 'outOfStock');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function lowStockProducts(int $threshold = 10, int $limit = 15): Collection
    {
        return Product::query()
            ->where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit($limit)
            ->get()
            ->map(fn (Product $p) => $this->productCard($p));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function outOfStockProducts(int $limit = 15): Collection
    {
        return Product::query()
            ->where('stock', '<=', 0)
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $p) => $this->productCard($p));
    }

    public function salesSummary(?string $from = null, ?string $to = null): array
    {
        $query = Order::query()->whereNull('deleted_at');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        } else {
            $query->whereDate('created_at', now()->toDateString());
        }

        $orders = $query->get();
        $count = $orders->count();
        $total = DecimalMath::round($orders->sum(fn ($o) => (float) ($o->total_after_discount ?: $o->total_price)));
        $paid = DecimalMath::round($orders->sum(fn ($o) => (float) $o->paid_at_sale));
        $remaining = DecimalMath::round($orders->sum(fn ($o) => (float) $o->remaining));
        $profit = DecimalMath::round($orders->sum(fn ($o) => (float) $o->profit));

        return [
            'period' => ($from && $to) ? "من {$from} إلى {$to}" : 'اليوم',
            'orders_count' => $count,
            'sales_total' => $total,
            'paid' => $paid,
            'remaining' => $remaining,
            'profit' => $profit,
        ];
    }

    public function clientsSummary(): array
    {
        $clients = Client::with(['orders.products', 'orders.payments'])->get();
        $withDebt = 0;
        $totalDebt = 0.0;

        foreach ($clients as $client) {
            $balance = (float) $client->remaining_balance;
            if ($balance > 0) {
                $withDebt++;
                $totalDebt += $balance;
            }
        }

        return [
            'clients_count' => $clients->count(),
            'with_debt' => $withDebt,
            'total_debt' => DecimalMath::round($totalDebt),
        ];
    }

    public function searchClients(?string $query = null, int $limit = 8): Collection
    {
        return Client::query()
            ->when($query, fn ($q) => $q->where('name', 'like', '%'.$query.'%'))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (Client $c) {
                $c->loadMissing(['orders.products', 'orders.payments']);

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => is_array($c->phone) ? implode(' / ', $c->phone) : (string) $c->phone,
                    'remaining' => DecimalMath::round((float) $c->remaining_balance),
                    'orders_count' => $c->orders->count(),
                ];
            });
    }

    public function suppliersSummary(): array
    {
        $unpaid = 0;
        try {
            $unpaid = PurchaseInvoice::query()
                ->whereRaw('COALESCE(total_amount,0) > COALESCE(paid_amount,0)')
                ->count();
        } catch (\Throwable) {
            $unpaid = 0;
        }

        return [
            'suppliers_count' => Supplier::count(),
            'invoices_count' => PurchaseInvoice::count(),
            'unpaid_invoices' => $unpaid,
        ];
    }

    public function expensesSummary(?string $from = null, ?string $to = null): array
    {
        $query = Expense::query();
        if ($from && $to) {
            $query->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        } else {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        $items = $query->get();

        return [
            'period' => ($from && $to) ? "من {$from} إلى {$to}" : 'هذا الشهر',
            'count' => $items->count(),
            'total' => DecimalMath::round($items->sum(fn ($e) => (float) ($e->amount ?? $e->price ?? 0))),
        ];
    }

    public function cashBalance(): array
    {
        $balance = 0.0;
        try {
            if (SchemaSafe::hasTable('cashes')) {
                $row = DB::table('cashes')->orderByDesc('id')->first();
                if ($row) {
                    $balance = (float) ($row->balance ?? $row->amount ?? 0);
                }
            }
        } catch (\Throwable) {
            $balance = 0.0;
        }

        $todayIn = 0.0;
        $todayOut = 0.0;
        try {
            if (SchemaSafe::hasTable('cash_transactions')) {
                $todayIn = (float) DB::table('cash_transactions')
                    ->whereDate('created_at', now()->toDateString())
                    ->whereIn('type', ['in', 'deposit', 'income', 'sale', 'add'])
                    ->sum('amount');
                $todayOut = (float) DB::table('cash_transactions')
                    ->whereDate('created_at', now()->toDateString())
                    ->whereIn('type', ['out', 'withdraw', 'expense', 'subtract'])
                    ->sum('amount');
            }
        } catch (\Throwable) {
            // ignore schema differences
        }

        return [
            'balance' => DecimalMath::round($balance),
            'today_in' => DecimalMath::round($todayIn),
            'today_out' => DecimalMath::round($todayOut),
        ];
    }

    public function categoriesSummary(): Collection
    {
        return Category::with('products')->orderBy('name')->get()->map(function (Category $cat) {
            $stockValue = $cat->products->sum(fn ($p) => (float) $p->stock * (float) $p->purchase_price);

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'products_count' => $cat->products->count(),
                'stock_value' => DecimalMath::round($stockValue),
            ];
        });
    }

    /**
     * دليل التقارير المتاحة في النظام مع الروابط.
     *
     * @return array<int, array{title: string, description: string, url: string, keywords: array<int, string>}>
     */
    public function reportsCatalog(): array
    {
        return [
            [
                'title' => 'ملخص المبيعات',
                'description' => 'إجمالي المبيعات والأرباح',
                'url' => route('dashboard.reports.summary'),
                'keywords' => ['مبيعات', 'ملخص', 'sales', 'summary'],
            ],
            [
                'title' => 'تقرير المبيعات المفصل',
                'description' => 'تفاصيل الطلبات والمبيعات',
                'url' => route('dashboard.reports.detailed'),
                'keywords' => ['مبيعات', 'مفصل', 'طلبات'],
            ],
            [
                'title' => 'تقرير المخزون / الجرد',
                'description' => 'قيمة المخزون والمنتجات',
                'url' => route('dashboard.reports.inventory.report'),
                'keywords' => ['مخزون', 'جرد', 'inventory', 'stock'],
            ],
            [
                'title' => 'تقرير الأرباح',
                'description' => 'الأرباح التفصيلية والإجمالية',
                'url' => route('dashboard.reports.profit_summary'),
                'keywords' => ['ربح', 'أرباح', 'profit'],
            ],
            [
                'title' => 'تقرير العملاء',
                'description' => 'أرصدة العملاء والمديونيات',
                'url' => route('dashboard.reports.reports.index'),
                'keywords' => ['عملاء', 'مديونية', 'clients'],
            ],
            [
                'title' => 'تقرير المشتريات',
                'description' => 'فواتير الشراء والموردين',
                'url' => route('dashboard.reports.purchases.index'),
                'keywords' => ['مشتريات', 'شراء', 'purchase'],
            ],
            [
                'title' => 'تقرير المصروفات',
                'description' => 'مصروفات الفترة',
                'url' => route('dashboard.reports.reports.expenses'),
                'keywords' => ['مصروف', 'مصروفات', 'expenses'],
            ],
            [
                'title' => 'تقرير الخزنة',
                'description' => 'حركة النقدية والرصيد',
                'url' => route('dashboard.reports.report.cash'),
                'keywords' => ['خزنة', 'نقدية', 'كاش', 'cash'],
            ],
            [
                'title' => 'تقرير الموردين',
                'description' => 'مديونيات الموردين',
                'url' => route('dashboard.reports.suppliers.index'),
                'keywords' => ['مورد', 'موردين', 'supplier'],
            ],
            [
                'title' => 'إنشاء طلب سريع',
                'description' => 'فتح شاشة البيع المباشر',
                'url' => route('dashboard.direct-sale'),
                'keywords' => ['طلب', 'بيع', 'فاتورة', 'order'],
            ],
        ];
    }

    public function findReport(string $query): array
    {
        $q = mb_strtolower(trim($query));
        $matches = [];

        foreach ($this->reportsCatalog() as $report) {
            $hay = mb_strtolower($report['title'].' '.$report['description'].' '.implode(' ', $report['keywords']));
            if ($q === '' || str_contains($hay, $q) || collect($report['keywords'])->contains(fn ($k) => str_contains($q, mb_strtolower($k)))) {
                $matches[] = $report;
            }
        }

        return $matches;
    }

    public function recentOrders(int $limit = 8): Collection
    {
        return Order::with('client')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number ?? $o->id,
                'client' => $o->client->name ?? 'بيع مباشر',
                'total' => DecimalMath::round((float) ($o->total_after_discount ?: $o->total_price)),
                'remaining' => DecimalMath::round((float) $o->remaining),
                'date' => optional($o->created_at)->format('Y-m-d H:i'),
                'url' => route('dashboard.orders.show', $o->id),
            ]);
    }
}

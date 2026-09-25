<?php

namespace App\Http\Controllers\Dashboard\Client;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CashService;
use App\Services\OrderLineNormalizer;
use App\Services\OrderFinancialService;
use App\Support\DecimalMath;
use App\Support\SaleUnits;


class OrderController extends Controller
{
    // =================== إنشاء طلب جديد ===================
    public function create(Client $client)
    {
        $categories = Category::with('products')->get();
        if (!$client || !$client->exists) {
            // العميل الافتراضي
            $client = $this->getDefaultClient();
        }

        $orders = $client->orders()->with('products', 'payments')->paginate(5);

        return view('dashboard.clients.orders.create', compact('client', 'categories', 'orders'));
    }

    // =================== حفظ الطلب ===================

    public function storeDirectSale(Request $request, OrderLineNormalizer $normalizer)
    {
        $client = $this->getDefaultClient();

        return $this->store($request, $client->id, $normalizer);
    }

    public function store(Request $request, $client, OrderLineNormalizer $normalizer)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'paid_at_sale' => 'nullable|numeric|min:0',
            'invoice_discount' => 'nullable|numeric|min:0',
        ], [
            'products.required' => 'يجب اختيار منتج واحد على الأقل.',
            'products.min' => 'يجب اختيار منتج واحد على الأقل.',
            'paid_at_sale.numeric' => 'المدفوع يجب أن يكون رقمًا.',
            'paid_at_sale.min' => 'المدفوع لا يمكن أن يكون سالبًا.',
            'invoice_discount.numeric' => 'الخصم يجب أن يكون رقمًا.',
            'invoice_discount.min' => 'الخصم لا يمكن أن يكون سالبًا.',
        ]);

        $products = $normalizer->fromRequest($request);

        if (empty($products)) {
            return back()
                ->withInput()
                ->with('error', 'يجب إدخال كمية واحدة على الأقل في أحد الوحدات.');
        }

        $request->merge(['products' => $products]);

        $client = Client::findOrFail($client);

        $total_price = 0;
        $hasKilo = false;

        // تحقق من المخزون باستخدام الكميات المحوّلة للحبة (أو الكيلو)
        foreach ($products as $productId => $data) {
            $product = Product::findOrFail($productId);

            $quantity = DecimalMath::round($data['quantity'] ?? 0);
            $lineTotal = (float) ($data['line_total'] ?? DecimalMath::mul($data['sale_price'] ?? 0, $quantity));

            if ($stockError = $this->stockShortageMessage($product, $quantity)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $stockError);
            }

            if (SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null) === SaleUnits::UNIT_KILO) {
                $hasKilo = true;
                $total_price += DecimalMath::round($lineTotal);
            } else {
                $total_price += DecimalMath::money($lineTotal);
            }
        }
        $total_price = $hasKilo ? DecimalMath::round($total_price) : DecimalMath::money($total_price);

        $paidAtSale = (float) ($request->paid_at_sale ?? 0);
        $invoiceDiscount = (float) ($request->invoice_discount ?? 0);
        if (! $hasKilo) {
            $paidAtSale = DecimalMath::money($paidAtSale);
            $invoiceDiscount = DecimalMath::money($invoiceDiscount);
        }
        $totalAfterDiscount = max($total_price - $invoiceDiscount, 0);

        if ($invoiceDiscount > $total_price + 0.0005) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الخصم ($invoiceDiscount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
        }

        if ($paidAtSale > $totalAfterDiscount + 0.0005) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("المدفوع ($paidAtSale) لا يمكن أن يكون أكبر من الإجمالي بعد الخصم ($totalAfterDiscount)"));
        }

        $remaining = max($totalAfterDiscount - $paidAtSale, 0);

        if ($client->name === 'زبون مباشر' && $remaining > 0) {
            return back()
                ->withInput()
                ->with('error', 'زبون مباشر يجب أن يدفع المبلغ كاملاً الآن (المدفوع = الإجمالي بعد الخصم).');
        }

        $orderNumber = $this->generateUniqueOrderNumber();

        try {
            $order = $this->attach_order($request, $client, $orderNumber, $paidAtSale, $invoiceDiscount);
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.orders.index')
            ->with('success', __('تم الإضافة بنجاح'))
            ->with('order_id', $order->id);
    }


public function update(Request $request, Client $client, Order $order, CashService $cashService, OrderLineNormalizer $normalizer)
{
    $request->validate([
        'products' => 'required|array|min:1',
        'paid_at_sale' => 'nullable|numeric|min:0',
        'invoice_discount' => 'nullable|numeric|min:0',
    ]);

    $products = $normalizer->fromRequest($request);

    if (empty($products)) {
        return back()
            ->withInput()
            ->with('error', 'يجب إدخال كمية واحدة على الأقل في أحد الوحدات.');
    }

    $request->merge(['products' => $products]);

    $paidAtSale = (float) ($request->paid_at_sale ?? 0);
    $invoiceDiscount = (float) ($request->invoice_discount ?? 0);
    $oldTransaction = CashTransaction::where('order_id', $order->id)->first();
    $oldDiscount = $oldTransaction?->amount ?? 0;

    $difference = $paidAtSale - $oldDiscount;
    $currentBalance = $cashService->getBalance();

    // ✅ التحقق من الرصيد في الخزينة
    if ($difference > 0) {
        // العميل دفع زيادة ⇒ لازم نتأكد الخزينة عندها كفاية
        if ($currentBalance < $difference) {
            return redirect()->back()
                ->withInput()
                ->with('error', "⚠️ الرصيد في الصندوق غير كافٍ لإضافة الفرق ({$difference})، الرصيد الحالي: {$currentBalance}");
        }
    } elseif ($difference < 0) {
        // العميل دفع أقل ⇒ لازم نرجع فرق للخزينة
        $refundAmount = abs($difference);
        if ($currentBalance < $refundAmount) {
            return redirect()->back()
                ->withInput()
                ->with('error', "⚠️ الرصيد في الصندوق غير كافٍ لإرجاع المبلغ ({$refundAmount})، الرصيد الحالي: {$currentBalance}");
        }
    }

    // ✅ حساب الإجمالي والربح
    $total_price = 0;
    $total_profit = 0;
    $productData = [];
    $hasKilo = false;

    foreach ($products as $productId => $data) {
        $quantity = DecimalMath::round($data['quantity'] ?? 0);
        $unitPrice = max(0, (float) ($data['sale_price'] ?? 0));
        $lineTotal = (float) ($data['line_total'] ?? ($unitPrice * $quantity));
        $product = Product::findOrFail($productId);
        $oldQuantity = (float) ($order->products->find($productId)?->pivot->quantity ?? 0);
        $available_stock = DecimalMath::add((float) $product->stock, $oldQuantity);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);

        if ($stockError = $this->stockShortageMessage($product, $quantity, $available_stock)) {
            return redirect()->back()
                ->withInput()
                ->with('error', $stockError);
        }

        if ($measure === SaleUnits::UNIT_KILO) {
            $hasKilo = true;
            $lineTotal = DecimalMath::round($lineTotal);
        } else {
            $lineTotal = DecimalMath::money($lineTotal);
        }

        $productData[$productId] = [
            'quantity'   => $quantity,
            'sale_price' => $unitPrice,
            'cost_price' => $product->purchase_price,
            'line_total' => $lineTotal,
        ];

        $total_price  += $lineTotal;
        $total_profit += $lineTotal - ((float) $product->purchase_price * $quantity);
    }
    $total_price = $hasKilo ? DecimalMath::round($total_price) : DecimalMath::money($total_price);
    if (! $hasKilo) {
        $paidAtSale = DecimalMath::money($paidAtSale);
        $invoiceDiscount = DecimalMath::money($invoiceDiscount);
    }
     if ($invoiceDiscount > $total_price + 0.0005) {
        return redirect()->back()
            ->withInput()
            ->with('error', __("الخصم ($invoiceDiscount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
    }
 $total_after_discount = max($total_price - $invoiceDiscount, 0);
    if ($paidAtSale > $total_after_discount + 0.0005) {
        return redirect()->back()
            ->withInput()
            ->with('error', __("المدفوع ($paidAtSale) لا يمكن أن يكون أكبر من الإجمالي بعد الخصم ($total_after_discount)"));
    }

    $remaining = max($total_after_discount - $paidAtSale, 0);
    $total_profit = $total_profit - $invoiceDiscount;

    $order->update([
        'paid_at_sale' => $paidAtSale,
        'invoice_discount' => $invoiceDiscount,
        'total_price' => $total_price,
        'remaining' => $remaining,
        'total_after_discount' => $total_after_discount,
        'profit' => floor($total_profit),
    ]);

    // ✅ تحديث المخزون: أعد كل الكميات القديمة ثم اخصم الجديدة
    foreach ($order->products as $product) {
        $product->update([
            'stock' => \App\Support\DecimalMath::add(
                (float) $product->stock,
                (float) $product->pivot->quantity
            ),
        ]);
    }

    foreach ($productData as $productId => $data) {
        $product = Product::findOrFail($productId);
        $product->update([
            'stock' => \App\Support\DecimalMath::sub(
                (float) $product->stock,
                (float) $data['quantity']
            ),
        ]);
    }

    $order->products()->sync($productData);

    // ✅ تحديث أو إنشاء حركة الخزينة
    $existingOrderTransaction = CashTransaction::where('order_id', $order->id)->first();

    if ($paidAtSale > 0) {
        if ($existingOrderTransaction) {
            $cashService->updateTransaction(
                $existingOrderTransaction,
                $paidAtSale,
                "تحديث الدفعيات على الطلب رقم #{$order->order_number} من العميل {$client->name}",
                'order',
                now()
            );
        } else {
            $cashService->record(
                'add',
                $paidAtSale,
                CashService::orderSalePaymentDescription($order, $paidAtSale),
                'order',
                now(),
                $order->id
            );
        }
    } elseif ($existingOrderTransaction) {
        $cashService->deleteTransaction($existingOrderTransaction);
    }

    app(OrderFinancialService::class)->recordInitialPayment($order, $paidAtSale);

    return redirect()->route('dashboard.orders.index')
        ->with('success', __('تم تعديل الطلب بنجاح'))
        ->with('order_id', $order->id);
}



    /**
     * توليد رقم طلب فريد بصيغة SU-XXXXX
     */
    protected function generateUniqueOrderNumber($prefix = 'SU-')
    {
        do {
            $number = $prefix . mt_rand(10000, 99999); // رقم عشوائي
        } while (\App\Models\Order::where('order_number', $number)->exists());

        return $number;
    }

    // =================== العميل الافتراضي ===================
    private function getDefaultClient()
    {
        return Client::firstOrCreate(
            ['name' => 'زبون مباشر'],
            [
                'phone' => '0912345678',
                'address' => 'عميل مباشر من المحل', // قيمة افتراضية
            ]
        );
    }

    // =================== تعديل الطلب ===================
    public function edit(Client $client, Order $order)
    {
        $categories = Category::with('products')->get();
        $orders = $client->orders()->with('products', 'payments')->paginate(5);
        return view('dashboard.clients.orders.edit', compact('client', 'order', 'categories', 'orders'));
    }

    private function attach_order($request, $client, $orderNumber = null, $paidAtSale = 0, $invoiceDiscount = 0)
    {
        $paidAtSale = max(0, $paidAtSale);
        $invoiceDiscount = max(0, $invoiceDiscount);

        $total_price = 0;
        $productData = [];
        $total_profit = 0;
        $hasKilo = false;

        // المنتجات مفترضة مُطبَّعة مسبقاً في store() — لا نُرجع RedirectResponse من هنا
        foreach ($request->products as $productId => $data) {
            $quantity = DecimalMath::round($data['quantity'] ?? 0);
            $unitPrice = max(0, (float) ($data['sale_price'] ?? 0));
            $lineTotal = (float) ($data['line_total'] ?? ($unitPrice * $quantity));

            $product = Product::findOrFail($productId);
            $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);

            if ($stockError = $this->stockShortageMessage($product, $quantity)) {
                throw new \RuntimeException($stockError);
            }

            if ($measure === SaleUnits::UNIT_KILO) {
                $hasKilo = true;
                $lineTotal = DecimalMath::round($lineTotal);
            } else {
                $lineTotal = DecimalMath::money($lineTotal);
            }

            $productData[$productId] = [
                'quantity' => $quantity,
                'sale_price' => $unitPrice,
                'cost_price' => (float) $product->purchase_price,
                'line_total' => $lineTotal,
            ];

            $total_price += $lineTotal;
            $total_profit += $lineTotal - ((float) $product->purchase_price * $quantity);
        }
        $total_price = $hasKilo ? DecimalMath::round($total_price) : DecimalMath::money($total_price);
        if (! $hasKilo) {
            $paidAtSale = DecimalMath::money($paidAtSale);
            $invoiceDiscount = DecimalMath::money($invoiceDiscount);
        }
        $total_after_discount = max($total_price - $invoiceDiscount, 0);
        $remaining = max($total_after_discount - $paidAtSale, 0);
        $total_profit = floor(max($total_profit - $invoiceDiscount, 0));

        $order = $client->orders()->create([
            'order_number' => $orderNumber ?? $this->generateUniqueOrderNumber(),
            'paid_at_sale' => $paidAtSale,
            'total_price' => $total_price,
            'remaining' => $remaining,
            'profit' => $total_profit,
            'total_after_discount' => $total_after_discount,
            'client_id' => $client->id,
            'invoice_discount' => $invoiceDiscount,
        ]);

        // ربط المنتجات وتحديث المخزون
        foreach ($productData as $productId => $data) {
            $order->products()->attach($productId, [
                'quantity' => $data['quantity'],
                'sale_price' => $data['sale_price'],
                'cost_price' => $data['cost_price'],
                'line_total' => $data['line_total'],
            ]);

            $product = Product::findOrFail($productId);
            $product->update([
                'stock' => DecimalMath::sub((float) $product->stock, (float) $data['quantity']),
            ]);
        }
        // ======================
        // إضافة الخصم إلى الخزينة إذا كان أكبر من 0
        // ======================
        if ($paidAtSale > 0) {
            $cashService = app(\App\Services\CashService::class);
            $order->load('client');
            $cashService->record(
                'add',
                $paidAtSale,
                CashService::orderSalePaymentDescription($order, $paidAtSale),
                'order',
                now(),
                $order->id
            );
            app(OrderFinancialService::class)->recordInitialPayment($order, $paidAtSale);
        }

        session()->flash('success', __('تم إضافة الطلب بنجاح'));
        return $order;
    }

    /**
     * رسالة نقص مخزون واضحة (حبة/كرتونة) أو null إذا المتاح يكفي.
     */
    protected function stockShortageMessage(Product $product, float $requested, ?float $available = null): ?string
    {
        $available ??= (float) $product->stock;
        $requested = DecimalMath::round($requested);
        $available = DecimalMath::round($available);

        // نسمح بالمساواة مع هامش كسور بسيط
        if ($requested <= $available + 0.0005) {
            return null;
        }

        $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = $product->sale_mode ?? null;
        $measure = $product->measure_unit ?? null;

        $requestedLabel = SaleUnits::formatQuantityLabel($requested, $bulk, $mode, $measure);
        $availableLabel = SaleUnits::formatQuantityLabel($available, $bulk, $mode, $measure);

        $hint = '';
        if (SaleUnits::normalizeMeasureUnit($measure) !== SaleUnits::UNIT_KILO && $bulk > 1) {
            $maxCartons = DecimalMath::div($available, $bulk);
            $hint = ' — أقصى كراتين يمكن بيعها الآن: '.DecimalMath::display($maxCartons);
        }

        return "الكمية المطلوبة للمنتج «{$product->name}» أكبر من المتاح.\n"
            ."طلبت: {$requestedLabel}\n"
            ."المتاح: {$availableLabel}{$hint}";
    }

    // =================== استرجاع المخزون عند حذف الطلب ===================
    private function detach_order($order)
    {
        foreach ($order->products as $product) {
            $product->update([
                'stock' => $product->stock + $product->pivot->quantity,
            ]);
        }

        $order->delete();
    }
}

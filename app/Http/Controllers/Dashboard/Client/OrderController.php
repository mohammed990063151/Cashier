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
use Illuminate\Support\Facades\Log;


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

        // تحقق إضافي من الكمية وحساب الإجمالي بالسعر الجديد
        foreach ($request->products as $productId => $data) {
            $product = Product::findOrFail($productId);

            $quantity = max(0, $data['quantity']);
            $sale_price = max(0, $data['sale_price']); // السعر الجديد المدخل

            if ($quantity > $product->stock) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', __("الكمية المطلوبة للمنتج '{$product->name}' أكبر من المخزون المتاح ({$product->stock})"));
            }

            $total_price += $sale_price * $quantity;
        }

        $paidAtSale = (float) ($request->paid_at_sale ?? 0);
        $invoiceDiscount = (float) ($request->invoice_discount ?? 0);
        $totalAfterDiscount = max($total_price - $invoiceDiscount, 0);

        if ($invoiceDiscount > $total_price) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الخصم ($invoiceDiscount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
        }

        if ($paidAtSale > $totalAfterDiscount) {
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
        $order = $this->attach_order($request, $client, $orderNumber, $paidAtSale, $invoiceDiscount);


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

    foreach ($request->products as $productId => $data) {
        $quantity = max(0, $data['quantity']);
        $unitPrice = max(0, $data['sale_price']);
        $product = Product::findOrFail($productId);
        $oldQuantity = $order->products->find($productId)?->pivot->quantity ?? 0;
        $available_stock = $product->stock + $oldQuantity;

        if ($quantity > $available_stock) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الكمية المطلوبة للمنتج '{$product->name}' أكبر من المخزون المتاح ({$available_stock})"));
        }

        $productData[$productId] = [
            'quantity'   => $quantity,
            'sale_price' => $unitPrice,
            'cost_price' => $product->purchase_price, // نحفظ سعر الشراء في pivot
        ];

        $total_price  += $unitPrice * $quantity;
        $total_profit += ($unitPrice - $product->purchase_price) * $quantity;
    }
     if ($invoiceDiscount > $total_price) {
        return redirect()->back()
            ->withInput()
            ->with('error', __("الخصم ($invoiceDiscount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
    }
 $total_after_discount = max($total_price - $invoiceDiscount, 0);
    if ($paidAtSale > $total_after_discount) {
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

    // ✅ تحديث المخزون
    foreach ($productData as $productId => $data) {
        $product = Product::findOrFail($productId);
        $oldQuantity = $order->products->find($productId)?->pivot->quantity ?? 0;
        $product->stock += $oldQuantity;
        $product->stock -= $data['quantity'];
        $product->save();
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

        // تحقق من الكميات وصلاحية الطلب
        foreach ($request->products as $productId => $data) {
            $quantity = max(0, $data['quantity']); // لا يقبل كمية سالبة
            $unitPrice = max(0, $data['sale_price']); // السعر المعدل من المستخدم

            $product = Product::findOrFail($productId);

            if ($quantity > $product->stock) {
                session()->flash('error', "الكمية المطلوبة للمنتج '{$product->name}' أكبر من المخزون المتاح ({$product->stock})");
                return redirect()->back()->withInput();
            }

            $productData[$productId] = [
                'quantity' => $quantity,
                'sale_price' => $unitPrice
            ];

            $total_price += $unitPrice * $quantity;
            $total_profit += ($unitPrice - $product->purchase_price) * $quantity;

            // $total_profit = floor($total_profit);
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
                'cost_price' => $product->purchase_price,
            ]);

            $product = Product::findOrFail($productId);
            $product->update([
                'stock' => $product->stock - $data['quantity'],
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

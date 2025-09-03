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
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    // =================== إنشاء طلب جديد ===================
    public function create(Client $client)
    {
        $categories = Category::with('products')->get();

        // العميل الافتراضي
        $client = $this->getDefaultClient();

        $orders = $client->orders()->with('products', 'payments')->paginate(5);

        return view('dashboard.clients.orders.create', compact('client', 'categories', 'orders'));
    }

    // =================== حفظ الطلب ===================

    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.sale_price' => 'required|numeric|min:0', // السعر الجديد لكل منتج
            'discount' => 'nullable|numeric|min:0',
        ]);

        $client = $this->getDefaultClient();

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

        // تحقق من أن الخصم لا يتجاوز الإجمالي
        $discount = $request->discount ?? 0;
        if ($discount > $total_price) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الخصم ($discount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
        }

        // توليد رقم طلب فريد
        $orderNumber = $this->generateUniqueOrderNumber();

        // تمرير الطلب مع السعر الجديد لحساب الإجمالي والفائدة
    $order = $this->attach_order($request, $client, $orderNumber, $discount);


   return redirect()->route('dashboard.orders.index')
    ->with('success', __('تم الإضافة بنجاح'))
    ->with('order_id', $order->id);
    }
    public function update(Request $request, Client $client, Order $order, CashService $cashService)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.sale_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

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
                'quantity' => $quantity,
                'sale_price' => $unitPrice,
            ];

            $total_price += $unitPrice * $quantity;
            $total_profit += ($unitPrice - $product->purchase_price) * $quantity;
        }

        $discount = $request->discount ?? 0;
        if ($discount > $total_price) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الخصم ($discount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
        }

        $remaining = max($total_price - $discount, 0);

        // تحديث بيانات الطلب
        $order->update([
            'discount' => $discount,
            'total_price' => $total_price,
            'remaining' => $remaining,
            'profit' => $total_profit,
        ]);

        // تحديث المنتجات والمخزون
        foreach ($productData as $productId => $data) {
            $product = Product::findOrFail($productId);
            $oldQuantity = $order->products->find($productId)?->pivot->quantity ?? 0;
            $product->stock += $oldQuantity;
            $product->stock -= $data['quantity'];
            $product->save();
        }

        $order->products()->sync($productData);

        // تسجيل الخصم في الخزينة أو تحديثه إذا كان موجودًا
        if ($discount > 0) {
            // البحث عن سجل خصم موجود لهذا الطلب
            $existingDiscountTransaction = CashTransaction::where('order_id', $order->id)
                ->first();

            if ($existingDiscountTransaction) {
                // تحديث السجل الموجود
                $cashService->updateTransaction(
                    $existingDiscountTransaction,
                    $discount,
                    "تحديث الدفعيات على الطلب رقم #{$order->order_number} من العميل {$client->name}",
                    'discount',
                    now()
                );
            } else {
                // إنشاء سجل جديد
                $cashService->record(
                    'add',
                    $discount,
                    "خصم على الطلب رقم #{$order->order_number}",
                    'discount',
                    now(),
                    $order->id
                );
            }
        }

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
                'address' => '-', // قيمة افتراضية
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

    private function attach_order($request, $client, $orderNumber = null, $discount = 0)
    {
        $discount = max(0, $discount); // لا يقبل خصم سالب

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
        }

        // تحقق من أن الخصم لا يجعل المتبقي سالب
        $remaining = max($total_price - $discount, 0);

        // إنشاء الطلب
        $order = $client->orders()->create([
            'order_number' => $orderNumber ?? $this->generateUniqueOrderNumber(),
            'discount'     => $discount,
            'total_price'  => $total_price,
            'remaining'    => $remaining,
            'profit'       => $total_profit,
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
        if ($discount > 0) {
            $cashService = app(\App\Services\CashService::class);
            Log::info('Order ID for CashService:', ['id' => $order->id]);
            $cashService->record(
                'add',
                $discount,
                "مدفوع من العميل للطلب #{$order->order_number}",
                'order',
                now(),
                $order->id
            );
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

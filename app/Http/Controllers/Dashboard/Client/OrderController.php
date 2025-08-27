<?php

namespace App\Http\Controllers\Dashboard\Client;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    $this->attach_order($request, $client, $orderNumber, $discount);

    return redirect()->route('dashboard.orders.index')
        ->with('success', __('تم الإضافة بنجاح'));
}


    public function update(Request $request, Client $client, Order $order)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $total_price = 0;

        foreach ($request->products as $productId => $data) {
            $product = Product::findOrFail($productId);

            // يسمح بإعادة الطلب السابق مع الكمية القديمة
            $available_stock = $product->stock + $order->products->find($productId)?->pivot->quantity;

            if ($data['quantity'] > $available_stock) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', __("الكمية المطلوبة للمنتج '{$product->name}' أكبر من المخزون المتاح ({$available_stock})"));
            }

            $total_price += $product->sale_price * $data['quantity'];
        }

        $discount = $request->discount ?? 0;
        if ($discount > $total_price) {
            return redirect()->back()
                ->withInput()
                ->with('error', __("الخصم ($discount) لا يمكن أن يكون أكبر من إجمالي الطلب ($total_price)"));
        }

        $this->detach_order($order);
        $this->attach_order($request, $client, $order->order_number, $discount);

        return redirect()->route('dashboard.orders.index')
            ->with('success', __('تم التعديل بنجاح'));
    }


    /**
     * توليد رقم طلب فريد بصيغة SU-XXXXX
     */
    protected function generateUniqueOrderNumber()
    {
        do {
            $randomNumber = 'SU-' . mt_rand(10000, 99999);
        } while (Order::where('order_number', $randomNumber)->exists());

        return $randomNumber;
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

    // =================== تحديث الطلب ===================
    // public function update(Request $request, Client $client, Order $order)
    // {
    //     $request->validate([
    //         'products' => 'required|array',
    //         'discount' => 'nullable|numeric|min:0',
    //     ]);

    //     // إعادة المخزون كما كان وحذف الطلب القديم
    //     $this->detach_order($order);

    //     // إنشاء طلب جديد بنفس رقم الطلب القديم
    //     $this->attach_order($request, $client, $order->order_number, $request->discount);

    //     session()->flash('success', __('تم التعديل بنجاح'));
    //     return redirect()->route('dashboard.orders.index');
    // }

    // =================== إضافة الطلب وربطه بالمنتجات ===================
    // private function attach_order($request, $client, $orderNumber = null, $discount = 0)
    // {
    //     // إنشاء الطلب
    //     $order = $client->orders()->create([
    //         'order_number' => $orderNumber,
    //         'discount'     => $discount,
    //     ]);

    //     // ربط المنتجات بالطلب
    //     foreach ($request->products as $productId => $data) {
    //         $quantity = $data['quantity'] ?? 1;
    //         $order->products()->attach($productId, ['quantity' => $quantity]);
    //     }

    //     // حساب إجمالي السعر
    //     $total_price = 0;
    //     foreach ($request->products as $id => $quantity) {
    //         $product = Product::findOrFail($id);
    //         $total_price += $product->sale_price * $quantity['quantity'];

    //         // تقليل المخزون
    //         $product->update([
    //             'stock' => $product->stock - $quantity['quantity'],
    //         ]);
    //     }

    //     // حساب المتبقي
    //     $remaining = max($total_price - $discount, 0);

    //     // تحديث الطلب
    //     $order->update([
    //         'total_price' => $total_price,
    //         'remaining'   => $remaining,
    //     ]);
    // }
    // private function attach_order($request, $client, $orderNumber = null, $discount = 0)
    // {
    //     $discount = max(0, $discount); // لا يقبل خصم سالب

    //     $total_price = 0;
    //     $productData = [];

    //     // تحقق من الكميات وصلاحية الطلب
    //     foreach ($request->products as $productId => $data) {
    //         $quantity = max(0, $data['quantity']); // لا يقبل كمية سالبة

    //         $product = Product::findOrFail($productId);

    //         if ($quantity > $product->stock) {
    //             session()->flash('error', "الكمية المطلوبة للمنتج '{$product->name}' أكبر من المخزون المتاح ({$product->stock})");
    //             return redirect()->back()->withInput();
    //         }

    //         $productData[$productId] = $quantity;
    //         $total_price += $product->sale_price * $quantity;
    //     }

    //     // تحقق من أن الخصم لا يجعل المتبقي سالب
    //     $remaining = max($total_price - $discount, 0);

    //     // إنشاء الطلب
    //     $order = $client->orders()->create([
    //         'order_number' => $orderNumber,
    //         'discount'     => $discount,
    //         'total_price'  => $total_price,
    //         'remaining'    => $remaining,
    //     ]);

    //     // ربط المنتجات وتحديث المخزون
    //     foreach ($productData as $productId => $quantity) {
    //         $order->products()->attach($productId, ['quantity' => $quantity]);

    //         $product = Product::findOrFail($productId);
    //         $product->update([
    //             'stock' => $product->stock - $quantity,
    //              'sale_price' => $product->sale_price ,
    //         ]);
    //     }

    //     session()->flash('success', __('تم إضافة الطلب بنجاح'));
    // }

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
        'order_number' => $orderNumber,
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
        ]);

        $product = Product::findOrFail($productId);
        $product->update([
            'stock' => $product->stock - $data['quantity'],
        ]);
    }

    session()->flash('success', __('تم إضافة الطلب بنجاح'));
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

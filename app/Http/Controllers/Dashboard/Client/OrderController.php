<?php

namespace App\Http\Controllers\Dashboard\Client;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function create(Client $client)
    {
        $categories = Category::with('products')->get();

    // العميل الافتراضي
    $client = $this->getDefaultClient();

    $orders = $client->orders()->with('products')->paginate(5);

    return view('dashboard.clients.orders.create', compact('client', 'categories', 'orders'));

    }//end of create

public function store(Request $request)
{
    $request->validate([
        'products' => 'required|array',
    ]);

    $client = $this->getDefaultClient();

    // توليد رقم طلب فريد
    $orderNumber = $this->generateUniqueOrderNumber();

    // تمرير الرقم الفريد عند إضافة الطلب
    $this->attach_order($request, $client, $orderNumber);

    session()->flash('success', __('تم الإضافة بنجاح'));
    return redirect()->route('dashboard.orders.index');
}

/**
 * توليد رقم طلب فريد بصيغة SU-XXXX
 */
protected function generateUniqueOrderNumber()
{
    do {
       $randomNumber = 'SU-' . mt_rand(10000, 99999); // مثال: SU-A1B2
    } while (\App\Models\Order::where('order_number', $randomNumber)->exists());

    return $randomNumber;
}

    private function getDefaultClient()
{
    $client = Client::firstOrCreate(
        ['name' => 'زبون مباشر'],
       [
        'phone' => '0912345678',
        'address' => '-', // قيمة افتراضية
    ]
    );
    return $client;
}

    public function edit(Client $client, Order $order)
    {
        $categories = Category::with('products')->get();
        $orders = $client->orders()->with('products')->paginate(5);
        return view('dashboard.clients.orders.edit', compact('client', 'order', 'categories', 'orders'));

    }//end of edit

    public function update(Request $request, Client $client, Order $order)
    {
        $request->validate([
            'products' => 'required|array',
        ]);

        $this->detach_order($order);

        $this->attach_order($request, $client , $order->order_number);

        session()->flash('success', __('تم التعديل بنجاح'));
        return redirect()->route('dashboard.orders.index');

    }//end of update

    private function attach_order($request, $client ,$orderNumber = null)
    {
        $order = $client->orders()->create([
            'order_number' => $orderNumber,
        ]);

        $order->products()->attach($request->products);

        $total_price = 0;

        foreach ($request->products as $id => $quantity) {

            $product = Product::FindOrFail($id);
            $total_price += $product->sale_price * $quantity['quantity'];

            $product->update([
                'stock' => $product->stock - $quantity['quantity']
            ]);

        }//end of foreach

        $order->update([
            'total_price' => $total_price
        ]);

    }//end of attach order

    private function detach_order($order)
    {
        foreach ($order->products as $product) {

            $product->update([
                'stock' => $product->stock + $product->pivot->quantity
            ]);

        }//end of for each

        $order->delete();

    }//end of detach order

}//end of controller

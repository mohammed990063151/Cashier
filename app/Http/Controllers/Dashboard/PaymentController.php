<?php
// PaymentController.php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;

class PaymentController extends Controller
{
   

    public function index(Request $request)
{
    $query = Order::with(['client', 'payments']);

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('order_number', 'like', "%$search%")
              ->orWhereHas('client', function($q) use ($search) {
                  $q->where('name', 'like', "%$search%");
              });
    }

    $orders = $query->paginate(15);

    return view('dashboard.payments.index', compact('orders'));
}


    // إضافة دفعة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Payment::create($request->all());

        // تحديث المبلغ المتبقي في الطلب
        $order = Order::find($request->order_id);
        $totalPaid = $order->payments()->sum('amount');
        $order->remaining = $order->total_price - $totalPaid;
        $order->save();

        return redirect()->route('dashboard.payments.index')->with('success', 'تم إضافة الدفعة بنجاح');
    }

    public function showPayments($id)
{
    $order = \App\Models\Order::with('payments')->findOrFail($id);

    $totalPaid = $order->payments->sum('amount');
    $remaining = $order->total_price - $totalPaid;

    return view('dashboard.payments.view_payments_modal', compact('order', 'totalPaid', 'remaining'));
}

}


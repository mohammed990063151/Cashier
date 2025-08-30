<?php
// PaymentController.php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashTransaction;
use App\Services\CashService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $cashService;

    public function __construct(CashService $cashService)
    {
        $this->cashService = $cashService;
    }


    public function index(Request $request)
    {
        $query = Order::with(['client', 'payments']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('order_number', 'like', "%$search%")
                ->orWhereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
        }

        $orders = $query->paginate(15);

        return view('dashboard.payments.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::with('payments')->findOrFail($request->order_id);

        // $totalPaid = $order->payments->sum('amount');
        $remaining = $order->remaining;

        if ($request->amount > $remaining) {
            return redirect()->back()->with('error', "المبلغ المدخل أكبر من المتبقي ({$remaining})")->withInput();
        }

        $payment = Payment::create($request->all());

        // تحديث المتبقي للطلب
        $order->remaining = $remaining - $request->amount;
        $order->save();

        // تسجيل الدفعة في صندوق الكاش
        try {
            $this->cashService->record('add', $request->amount, "دفعة طلب رقم {$order->order_number}", 'payment', now(), $order->id, $payment->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "خطأ في تسجيل الدفعة في الصندوق: {$e->getMessage()}")->withInput();
        }

        return redirect()->back()->with('success', 'تم إضافة الدفعة بنجاح');
    }


    public function editPayments($orderId)
    {
        $order = Order::with('payments')->findOrFail($orderId);
        return view('dashboard.payments.edit', compact('order'));
    }

public function update(Request $request, $paymentId)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'method' => 'required|in:cash,bank',
        'notes'  => 'nullable|string|max:255',
    ]);

    $payment = Payment::findOrFail($paymentId);
    $order   = $payment->order()->with('payments')->first();

    // مجموع الدفعات الأخرى بدون الدفعة الحالية
    $totalPaidExcludingCurrent = $order->payments()
        ->where('id', '!=', $paymentId)
        ->sum('amount');

    // المتبقي الفعلي مع الخصم
    $remaining = ($order->total_price - $order->discount) - $totalPaidExcludingCurrent;

    if ($request->amount > $remaining) {
        return back()->with('error', "المبلغ المدخل أكبر من المتبقي ({$remaining})")->withInput();
    }

    $oldAmount = (float) $payment->amount;
    $oldMethod = $payment->method;
    $newAmount = (float) $request->amount;
    $newMethod = $request->method;

    DB::beginTransaction();
    try {
        // ===== تحديث حركة الصندوق عبر CashService =====
       $cashService = app(\App\Services\CashService::class);

// الحصول على الحركة المرتبطة بالدفعة
$transaction = $payment->transaction;

if ($transaction) {
    // تعديل الحركة المالية عبر CashService
    $cashService->updateTransaction(
        $transaction,
        $newAmount,
        "تعديل دفعة لطلب {$order->order_number}",
        'payment',
        now()
    );
} else {
    // إنشاء حركة مالية جديدة عبر CashService
    $cashService->record(
        $newMethod === 'cash' ? 'add' : 'deduct',
        $newAmount,
        "دفعة جديدة لطلب {$order->order_number}",
        'payment',
        now(),
        $order->id
    )->update([
        'payment_id' => $payment->id
    ]);
}

        // ===== تحديث بيانات الدفع =====
        $payment->update([
            'amount' => $newAmount,
            'method' => $newMethod,
            'notes'  => $request->notes,
        ]);

        // ===== تحديث المبلغ المتبقي في الطلب =====
        $order->load('payments');
        $order->remaining = ($order->total_price - $order->discount) - $order->payments->sum('amount');
        $order->save();

        DB::commit();
        return back()->with('success', 'تم تعديل الدفعة بنجاح');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', "فشل تعديل الدفعة: {$e->getMessage()}")->withInput();
    }
}




    public function showPayments($id)
    {
        $order = \App\Models\Order::with('payments')->findOrFail($id);

        $totalPaid = $order->payments->sum('amount');
        $remaining = $order->remaining;

        return view('dashboard.payments.view_payments_modal', compact('order', 'totalPaid', 'remaining'));
    }
}

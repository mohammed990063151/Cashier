<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mpdf\Mpdf;
use App\Services\CashService;
use App\Services\OrderFinancialService;
use App\Services\OrderReturnService;
use App\Models\CashTransaction;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $cashService;

    protected OrderFinancialService $orderFinancial;

    public function __construct(CashService $cashService, OrderFinancialService $orderFinancial)
    {
        $this->cashService = $cashService;
        $this->orderFinancial = $orderFinancial;
    }

    public function show($id)
    {
        $order = Order::with(['products.category', 'client', 'payments', 'returns.items.product'])->findOrFail($id);
        $summary = $this->orderFinancial->summary($order);

        return view('dashboard.orders.order_details', $summary);
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $paymentStatus = $request->payment_status;

        $orders = Order::with(['client', 'payments', 'returns'])->withCount('products')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            });

        $this->orderFinancial->applyPaymentStatusFilter($orders, $paymentStatus);

        $orders = $orders->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('dashboard.orders.index', compact('orders', 'paymentStatus'));
    }

    public function products(Order $order)
    {
        $order->load(['products', 'client', 'payments', 'returns.items.product']);
        $summary = $this->orderFinancial->summary($order);

        return view('dashboard.orders._products', $summary);
    }

    public function generatePdf($orderId)
    {
        $order = Order::with(['products', 'client', 'payments', 'returns.items.product'])->findOrFail($orderId);
        $summary = $this->orderFinancial->summary($order);
        $setting = Setting::first();

        $logoPath = $setting && $setting->logo
            ? public_path(ltrim($setting->logo, '/'))
            : null;

        $productCount = $order->products->count();
        $breakdownLines = $order->products->sum(
            fn ($product) => count($this->orderFinancial->productUnitBreakdown($product))
        );
        $pageHeightMm = min(450, max(160, 95 + ($productCount * 28) + ($breakdownLines * 8)));

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [80, $pageHeightMm],
            'default_font' => 'dejavusans',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 4,
            'margin_bottom' => 4,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->SetDirectionality('rtl');

        $html = view('pdf.order-invoice', array_merge($summary, [
            'setting' => $setting,
            'logoPath' => $logoPath && file_exists($logoPath) ? $logoPath : null,
        ]))->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipt-'.$order->order_number.'.pdf"',
        ]);
    }

    public function destroy(Order $order, CashService $cashService)
    {
        $order->load('payments');

        foreach ($order->products as $product) {
            $product->update([
                'stock' => \App\Support\DecimalMath::add((float) $product->stock, (float) $product->pivot->quantity),
            ]);
        }

        $order->update([
            'total_return' => $order->total_price,
        ]);

        $cashService->deleteTransactionsForOrder($order);
        $order->delete();

        session()->flash('success', "تم حذف الطلب (#{$order->order_number}) مؤقتاً.");

        return redirect()->route('dashboard.orders.index');
    }

    public function softdelet()
    {
        $orders = Order::onlyTrashed()->with('client')->paginate(10);
        $balance = $this->cashService->getBalance();

        $hasProblem = $orders->contains(function ($order) use ($balance) {
            return $order->paid_at_sale > $balance;
        });

        if ($hasProblem) {
            session()->flash('error', '⚠️ يوجد طلب مدفوع أكبر من رصيد الخزينة الحالي!');
        }

        return view('dashboard.orders.trashed', compact('orders'));
    }

    public function restore($id, CashService $cashService)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();

        foreach ($order->products as $product) {
            $product->update([
                'stock' => \App\Support\DecimalMath::sub((float) $product->stock, (float) $product->pivot->quantity),
            ]);
        }

        $order->update([
            'total_return' => 0,
            'remaining' => $order->remaining,
            'profit' => $order->profit,
        ]);

        $transaction = CashTransaction::where('order_id', $order->id)->first();
        if ($transaction) {
            $cashService->updateTransaction(
                $transaction,
                $order->paid_at_sale,
                "استرجاع الدفعيات على الطلب رقم #{$order->order_number} من العميل {$order->client->name}",
                'order',
                now()
            );
        } elseif ($order->paid_at_sale > 0) {
            $cashService->record(
                'add',
                $order->paid_at_sale,
                "استرجاع الدفعيات على الطلب رقم #{$order->order_number} من العميل {$order->client->name}",
                'order',
                now(),
                $order->id
            );
        }

        session()->flash('success', "تم استرجاع الطلب بالكامل: #{$order->order_number}");

        return redirect()->route('dashboard.orders.index');
    }

    public function returnForm(Order $order)
    {
        $order->load(['products', 'client', 'returns.items.product']);
        $summary = $this->orderFinancial->summary($order, false);

        $alreadyRefundedCash = round((float) $order->returns->sum('refund_amount'), 2);

        return view('dashboard.orders._return_form', array_merge($summary, [
            'order' => $order,
            'alreadyRefundedCash' => $alreadyRefundedCash,
        ]));
    }

    public function returnStore(Request $request, Order $order, OrderReturnService $returnService)
    {
        $request->validate([
            'lines' => 'required|array',
            'notes' => 'nullable|string|max:500',
            'return_date' => 'nullable|date',
        ]);

        try {
            $orderReturn = $returnService->processReturn(
                $order,
                $request->input('lines', []),
                $request->notes,
                $request->return_date
            );

            $msg = "تم تسجيل المرتجع {$orderReturn->return_number} بنجاح.";
            if ($orderReturn->refund_amount > 0) {
                $msg .= ' تم خصم '.number_format($orderReturn->refund_amount, 2).' ج.س من الخزينة وإرجاعها للعميل.';
            } else {
                $msg .= ' تم تخفيض المتبقي على الطلب دون حركة نقدية (لم يُدفع مبلغ يُسترد).';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'return_number' => $orderReturn->return_number,
                ]);
            }

            return redirect()
                ->route('dashboard.orders.index')
                ->with('success', $msg)
                ->with('order_id', $order->id);
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'تحقق من البيانات المدخلة.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->route('dashboard.orders.index')
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            report($e);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->route('dashboard.orders.index')
                ->with('error', $e->getMessage());
        }
    }
}

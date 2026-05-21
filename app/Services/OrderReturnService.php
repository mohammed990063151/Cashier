<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReturnService
{
    public function __construct(
        protected OrderFinancialService $orderFinancial,
        protected CashService $cashService
    ) {}

    /**
     * @param  array<int, int>  $lines  product_id => quantity to return
     */
    public function processReturn(Order $order, array $lines, ?string $notes = null, ?string $returnDate = null): OrderReturn
    {
        $order->load(['products', 'payments', 'client', 'returns']);

        $returnDate = $returnDate ?? now()->toDateString();
        $built = $this->buildReturnLines($order, $lines);

        if ($built['items'] === []) {
            throw ValidationException::withMessages([
                'lines' => 'حدد كمية مرتجعة واحدة على الأقل.',
            ]);
        }

        $before = $this->orderFinancial->calculate($order);
        $itemsTotal = $built['items_total'];
        $alreadyRefunded = round((float) $order->returns->sum('refund_amount'), 2);
        $refundPlan = $this->planCashRefund($before, $itemsTotal, $alreadyRefunded);

        if ($refundPlan['refund_amount'] > 0 && $this->cashService->getBalance() < $refundPlan['refund_amount']) {
            throw ValidationException::withMessages([
                'refund' => 'رصيد الخزينة غير كافٍ لإرجاع المبلغ للعميل. الرصيد الحالي: '
                    .$this->cashService->getBalance().' ج.س — المطلوب: '.$refundPlan['refund_amount'].' ج.س',
            ]);
        }

        return DB::transaction(function () use (
            $order,
            $built,
            $notes,
            $returnDate,
            $itemsTotal,
            $refundPlan,
            $alreadyRefunded
        ) {
            $remainingReduced = $refundPlan['remaining_reduced'];
            foreach ($built['items'] as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $pivot = $order->products->find($product->id)->pivot;
                $newQty = (int) $pivot->quantity - $item['quantity'];

                if ($newQty > 0) {
                    $order->products()->updateExistingPivot($product->id, ['quantity' => $newQty]);
                } else {
                    $order->products()->detach($product->id);
                }

                $product->update(['stock' => $product->stock + $item['quantity']]);
            }

            $orderReturn = OrderReturn::create([
                'order_id' => $order->id,
                'return_number' => $this->nextReturnNumber(),
                'items_total' => $itemsTotal,
                'refund_amount' => 0,
                'remaining_reduced' => $remainingReduced,
                'notes' => $notes,
                'return_date' => $returnDate,
            ]);

            foreach ($built['items'] as $item) {
                $orderReturn->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $order->update([
                'total_return' => round((float) ($order->total_return ?? 0) + $itemsTotal, 2),
            ]);

            $order = $this->orderFinancial->syncOrderTotals($order->fresh(['products', 'payments', 'client', 'returns']));

            $after = $this->orderFinancial->calculate($order);
            $finalRefund = $this->cashRefundAfterTotals($after, $alreadyRefunded);

            if ($finalRefund > 0.009) {
                if ($this->cashService->getBalance() < $finalRefund) {
                    throw ValidationException::withMessages([
                        'refund' => 'رصيد الخزينة غير كافٍ بعد إعادة حساب الطلب. الرصيد الحالي: '
                            .$this->cashService->getBalance().' ج.س — المطلوب: '.$finalRefund.' ج.س',
                    ]);
                }

                $this->cashService->record(
                    'deduct',
                    $finalRefund,
                    CashService::orderReturnDescription($order, $finalRefund, $built['summary'], $orderReturn->return_number),
                    'returns',
                    $returnDate,
                    $order->id,
                    null,
                    null,
                    null,
                    null,
                    $orderReturn->id
                );

                $orderReturn->update(['refund_amount' => $finalRefund]);
            }

            return $orderReturn->fresh(['items.product', 'order.client']);
        });
    }

    /**
     * @param  array<int, int>  $lines
     * @return array{items: list<array{product: Product, quantity: int, unit_price: float, subtotal: float}>, items_total: float, summary: string}
     */
    protected function buildReturnLines(Order $order, array $lines): array
    {
        $items = [];
        $total = 0.0;
        $names = [];

        foreach ($lines as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $product = $order->products->find($productId);
            if (! $product) {
                throw ValidationException::withMessages([
                    "lines.{$productId}" => 'المنتج غير موجود في هذا الطلب.',
                ]);
            }

            $maxQty = (int) $product->pivot->quantity;
            if ($qty > $maxQty) {
                throw ValidationException::withMessages([
                    "lines.{$productId}" => "الكمية المرتجعة أكبر من المباعة ({$maxQty}).",
                ]);
            }

            $unitPrice = (float) $product->pivot->sale_price;
            $subtotal = round($unitPrice * $qty, 2);
            $total += $subtotal;
            $names[] = $product->name.' ×'.$qty;

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        return [
            'items' => $items,
            'items_total' => round($total, 2),
            'summary' => implode('، ', array_slice($names, 0, 3)).(count($names) > 3 ? '…' : ''),
        ];
    }

    /**
     * @param  array<int, int>  $lines
     * @return array{items_total: float, refund_amount: float, remaining_reduced: float}
     */
    public function preview(Order $order, array $lines): array
    {
        $order->load(['products', 'payments', 'returns']);
        $before = $this->orderFinancial->calculate($order);
        $built = $this->buildReturnLines($order, $lines);
        $itemsTotal = $built['items_total'];
        $alreadyRefunded = round((float) $order->returns->sum('refund_amount'), 2);
        $refundPlan = $this->planCashRefund($before, $itemsTotal, $alreadyRefunded);

        return [
            'items_total' => $itemsTotal,
            'refund_amount' => $refundPlan['refund_amount'],
            'remaining_reduced' => $refundPlan['remaining_reduced'],
            'already_refunded' => $alreadyRefunded,
            'summary' => $built['summary'],
        ];
    }

    /**
     * @param  array<string, mixed>  $financial
     * @return array{remaining_reduced: float, refund_amount: float}
     */
    protected function planCashRefund(array $financial, float $itemsTotal, float $alreadyRefunded): array
    {
        $remainingBefore = (float) $financial['remaining'];
        $remainingReduced = min($itemsTotal, $remainingBefore);

        $newTotalSale = max(0, (float) $financial['totalSale'] - $itemsTotal);
        $newAfterDiscount = max($newTotalSale - (float) $financial['invoiceDiscount'], 0);
        $refundAmount = $this->cashRefundAfterTotals([
            'totalPaid' => $financial['totalPaid'],
            'totalAfterDiscount' => $newAfterDiscount,
        ], $alreadyRefunded);

        return [
            'remaining_reduced' => $remainingReduced,
            'refund_amount' => $refundAmount,
        ];
    }

    /**
     * @param  array{totalPaid: float, totalAfterDiscount: float}  $totals
     */
    protected function cashRefundAfterTotals(array $totals, float $alreadyRefunded): float
    {
        $stillOwedToCustomer = max(
            0,
            round((float) $totals['totalPaid'] - (float) $totals['totalAfterDiscount'], 2)
        );

        return round(max(0, $stillOwedToCustomer - $alreadyRefunded), 2);
    }

    protected function nextReturnNumber(): string
    {
        $last = OrderReturn::orderByDesc('id')->value('return_number');
        $num = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return 'RET-'.str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }
}

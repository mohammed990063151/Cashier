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
                $newQty = \App\Support\DecimalMath::sub((float) $pivot->quantity, (float) $item['quantity']);

                if ($newQty > 0.0005) {
                    $order->products()->updateExistingPivot($product->id, ['quantity' => $newQty]);
                } else {
                    $order->products()->detach($product->id);
                }

                $product->update([
                    'stock' => \App\Support\DecimalMath::add((float) $product->stock, (float) $item['quantity']),
                ]);
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
     * @param  array<int, mixed>  $lines  product_id => pieces OR unit map
     * @return array{items: list<array{product: Product, quantity: float, unit_price: float, subtotal: float, quantity_label: string}>, items_total: float, summary: string}
     */
    protected function buildReturnLines(Order $order, array $lines): array
    {
        $items = [];
        $total = 0.0;
        $names = [];

        foreach ($lines as $productId => $line) {
            $product = $order->products->find($productId);
            if (! $product) {
                if ($this->lineHasQty($line)) {
                    throw ValidationException::withMessages([
                        "lines.{$productId}" => 'المنتج غير موجود في هذا الطلب.',
                    ]);
                }
                continue;
            }

            $bulk = max(1, (int) ($product->pieces_per_carton ?? 12));
            $mode = $product->sale_mode ?? null;
            $measure = $product->measure_unit ?? null;
            $piecePrice = (float) $product->pivot->sale_price;
            $maxQty = \App\Support\DecimalMath::round($product->pivot->quantity);

            [$qty, $subtotal] = $this->resolveReturnQuantity($line, $piecePrice, $bulk, $mode, $measure);

            if ($qty <= 0) {
                continue;
            }

            if ($qty > $maxQty + 0.0005) {
                $soldLabel = \App\Support\SaleUnits::formatQuantityLabel($maxQty, $bulk, $mode, $measure);
                throw ValidationException::withMessages([
                    "lines.{$productId}" => "الكمية المرتجعة أكبر من المباعة ({$soldLabel}).",
                ]);
            }

            $unitPrice = $qty > 0 ? \App\Support\DecimalMath::div($subtotal, $qty) : $piecePrice;
            $qtyLabel = \App\Support\SaleUnits::formatQuantityLabel($qty, $bulk, $mode, $measure);
            $total += $subtotal;
            $names[] = $product->name.' × '.$qtyLabel;

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'quantity_label' => $qtyLabel,
            ];
        }

        return [
            'items' => $items,
            'items_total' => \App\Support\DecimalMath::money($total),
            'summary' => implode('، ', array_slice($names, 0, 3)).(count($names) > 3 ? '…' : ''),
        ];
    }

    protected function lineHasQty(mixed $line): bool
    {
        if (is_numeric($line)) {
            return (float) $line > 0;
        }
        if (! is_array($line)) {
            return false;
        }
        foreach ($line as $unitData) {
            if (is_array($unitData) && (float) ($unitData['qty'] ?? 0) > 0) {
                return true;
            }
            if (is_numeric($unitData) && (float) $unitData > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: float, 1: float} [pieces, subtotal]
     */
    protected function resolveReturnQuantity(
        mixed $line,
        float $piecePrice,
        int $bulk,
        ?string $mode,
        ?string $measure
    ): array {
        // توافق قديم: رقم واحد = كمية بالحبة/الوحدة الأساسية
        if (is_numeric($line)) {
            $qty = \App\Support\DecimalMath::round($line);
            return [$qty, \App\Support\DecimalMath::mul($piecePrice, $qty)];
        }

        if (! is_array($line)) {
            return [0.0, 0.0];
        }

        $normalized = [];
        foreach ($line as $unitKey => $unitData) {
            if (is_array($unitData)) {
                $qty = max(0, (float) ($unitData['qty'] ?? 0));
                $price = isset($unitData['price'])
                    ? max(0, (float) $unitData['price'])
                    : \App\Support\SaleUnits::unitPriceForForm((string) $unitKey, $piecePrice, $bulk);
            } else {
                $qty = max(0, (float) $unitData);
                $price = \App\Support\SaleUnits::unitPriceForForm((string) $unitKey, $piecePrice, $bulk);
            }
            $normalized[$unitKey] = ['qty' => $qty, 'price' => $price];
        }

        $converted = \App\Support\SaleUnits::toPieceLine($normalized, $bulk, $mode, $measure);
        $pieces = (float) ($converted['quantity'] ?? 0);
        if ($pieces <= 0) {
            return [0.0, 0.0];
        }

        $subtotal = 0.0;
        foreach ($normalized as $unitKey => $unitData) {
            $subtotal += ((float) $unitData['qty']) * ((float) $unitData['price']);
        }

        return [$pieces, \App\Support\DecimalMath::round($subtotal)];
    }

    /**
     * @param  array<int, mixed>  $lines
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

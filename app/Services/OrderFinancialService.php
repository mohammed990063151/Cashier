<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Support\SaleUnits;

class OrderFinancialService
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Order $order): array
    {
        $order->loadMissing(['products', 'payments', 'returns']);

        $totalSale = (float) $order->products->sum(
            fn ($product) => $product->pivot->quantity * $product->pivot->sale_price
        );

        $paidAtSale = (float) ($order->paid_at_sale ?? 0);
        $invoiceDiscount = (float) ($order->invoice_discount ?? 0);

        if ($paidAtSale <= 0 && $invoiceDiscount > 0 && $invoiceDiscount >= $totalSale) {
            $paidAtSale = $invoiceDiscount;
            $invoiceDiscount = 0;
        }

        $totalAfterDiscount = max($totalSale - $invoiceDiscount, 0);
        $paymentsTotal = (float) $order->payments->sum('amount');
        $initialFromPayments = (float) $order->payments
            ->where('method', 'cash_at_sale')
            ->sum('amount');

        if ($paymentsTotal > 0) {
            $totalPaid = $paymentsTotal;
            $paidAtSale = $initialFromPayments > 0 ? $initialFromPayments : $paidAtSale;
            $paymentsSum = max(0, $paymentsTotal - $paidAtSale);
        } else {
            $paymentsSum = 0;
            $totalPaid = $paidAtSale;
        }

        $remaining = max($totalAfterDiscount - $totalPaid, 0);

        $totalPurchase = $order->products->sum(
            fn ($product) => $product->pivot->quantity * $product->pivot->cost_price
        );
        $profitBeforeDiscount = $totalSale - $totalPurchase;
        $profitAfterDiscount = $totalAfterDiscount - $totalPurchase;
        $profitPercentage = $totalPurchase > 0
            ? ($profitAfterDiscount / $totalPurchase) * 100
            : 0;

        $returnMetrics = $this->returnMetrics($order, $totalSale, $invoiceDiscount, $totalAfterDiscount, $totalPaid);

        return array_merge(compact(
            'order',
            'totalSale',
            'invoiceDiscount',
            'totalAfterDiscount',
            'paidAtSale',
            'paymentsSum',
            'totalPaid',
            'remaining',
            'totalPurchase',
            'profitBeforeDiscount',
            'profitAfterDiscount',
            'profitPercentage'
        ), $returnMetrics);
    }

    /**
     * @return array<string, mixed>
     */
    protected function returnMetrics(
        Order $order,
        float $totalSale,
        float $invoiceDiscount,
        float $totalAfterDiscount,
        float $totalPaid
    ): array {
        $totalReturnedMerchandise = round((float) ($order->total_return ?? 0), 2);
        $totalRefundedToCustomer = round((float) $order->returns->sum('refund_amount'), 2);
        $originalTotalSale = round($totalSale + $totalReturnedMerchandise, 2);
        $originalTotalAfterDiscount = max($originalTotalSale - $invoiceDiscount, 0);
        $hasReturns = $totalReturnedMerchandise > 0.009;
        $isFullyReturned = $hasReturns && ($totalSale <= 0.009 || $order->products->isEmpty());
        $overpaidAfterReturn = round(max(0, $totalPaid - $totalAfterDiscount), 2);

        return [
            'totalReturnedMerchandise' => $totalReturnedMerchandise,
            'totalRefundedToCustomer' => $totalRefundedToCustomer,
            'originalTotalSale' => $originalTotalSale,
            'originalTotalAfterDiscount' => $originalTotalAfterDiscount,
            'hasReturns' => $hasReturns,
            'isFullyReturned' => $isFullyReturned,
            'overpaidAfterReturn' => $overpaidAfterReturn,
        ];
    }

    public function hasReturns(Order $order): bool
    {
        return (float) ($order->total_return ?? 0) > 0.009
            || $order->returns()->exists();
    }

    public function syncOrderTotals(Order $order): Order
    {
        $data = $this->calculate($order);

        $updates = [
            'total_price' => $data['totalSale'],
            'paid_at_sale' => $data['paidAtSale'],
            'invoice_discount' => $data['invoiceDiscount'],
            'total_after_discount' => $data['totalAfterDiscount'],
            'remaining' => $data['remaining'],
            'profit' => floor(max($data['profitAfterDiscount'], 0)),
        ];

        if ($data['remaining'] <= 0.009) {
            $updates['payment_due_at'] = null;
            $updates['collection_notes'] = null;
            $order->paymentInstallments()->whereNull('paid_at')->delete();
        }

        $order->update($updates);

        return $order->fresh(['products', 'payments', 'client']);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Order $order, bool $persist = true): array
    {
        if ($persist) {
            $order = $this->syncOrderTotals($order);
        }

        return $this->calculate($order);
    }

    public function recordInitialPayment(Order $order, float $paidAtSale): void
    {
        if ($paidAtSale <= 0) {
            return;
        }

        $exists = $order->payments()
            ->where('amount', $paidAtSale)
            ->where('method', 'cash_at_sale')
            ->exists();

        if ($exists) {
            return;
        }

        Payment::create([
            'order_id' => $order->id,
            'amount' => $paidAtSale,
            'method' => 'cash_at_sale',
            'notes' => 'دفعة عند إنشاء الطلب',
        ]);
    }

    public function formatProductQuantity($product): string
    {
        $pieces = (float) $product->pivot->quantity;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));

        return SaleUnits::formatQuantityLabel(
            $pieces,
            $bulkSize,
            $product->sale_mode ?? null,
            $product->measure_unit ?? null
        );
    }

    /**
     * @return array{quantity: string, price: string, line_total: float}
     */
    public function formatProductSaleLine($product): array
    {
        $pieces = (float) $product->pivot->quantity;
        $piecePrice = (float) $product->pivot->sale_price;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);

        $quantityText = SaleUnits::formatQuantityLabel($pieces, $bulkSize, $mode, $measure);

        if ($measure === SaleUnits::UNIT_KILO) {
            $priceText = \App\Support\DecimalMath::display($piecePrice).' ج.س / كيلو';
        } elseif ($mode === SaleUnits::MODE_BULK_ONLY && $bulkSize > 1) {
            $priceText = \App\Support\DecimalMath::moneyDisplay(\App\Support\DecimalMath::money($piecePrice * $bulkSize)).' ج.س / كرتونة';
        } else {
            $priceText = \App\Support\DecimalMath::moneyDisplay($piecePrice).' ج.س / حبة';
        }

        return [
            'quantity' => $quantityText,
            'price' => $priceText,
            'line_total' => \App\Support\DecimalMath::money(\App\Support\DecimalMath::mul($pieces, $piecePrice)),
        ];
    }

    /**
     * @return array<int, array{label: string, count: float|int, pieces: float|int, piece_price: float, line_total: float}>
     */
    public function productUnitBreakdown($product): array
    {
        $pieces = (float) $product->pivot->quantity;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $piecePrice = (float) $product->pivot->sale_price;
        $lines = SaleUnits::breakdownLines(
            $pieces,
            $bulkSize,
            $product->sale_mode ?? null,
            $product->measure_unit ?? null
        );

        return array_map(function ($line) use ($piecePrice) {
            return array_merge($line, [
                'piece_price' => $piecePrice,
                'line_total' => \App\Support\DecimalMath::mul($line['pieces'], $piecePrice),
            ]);
        }, $lines);
    }

    public function paymentStatus(Order $order): string
    {
        $data = $this->calculate($order);

        if ($data['hasReturns']) {
            return $data['isFullyReturned'] ? 'returned' : 'partial_return';
        }

        $remaining = (float) $data['remaining'];
        $totalAfterDiscount = (float) $data['totalAfterDiscount'];
        $totalPaid = (float) $data['totalPaid'];

        if ($totalAfterDiscount <= 0.009 || $remaining <= 0.009) {
            return 'paid';
        }

        if ($totalPaid > 0.009) {
            return 'partial';
        }

        return 'unpaid';
    }

    public function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'returned' => 'مسترجع',
            'partial_return' => 'مرتجع جزئي',
            'paid' => 'مدفوع بالكامل',
            'partial' => 'دفع جزئي',
            'unpaid' => 'متبقي',
            default => 'غير محدد',
        };
    }

    public function paymentStatusClass(string $status): string
    {
        return match ($status) {
            'returned' => 'label-info',
            'partial_return' => 'label-primary',
            'paid' => 'label-success',
            'partial' => 'label-warning',
            'unpaid' => 'label-danger',
            default => 'label-default',
        };
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function applyPaymentStatusFilter($query, ?string $status): void
    {
        if (! $status || $status === 'all') {
            return;
        }

        match ($status) {
            'returned' => $query->where('total_return', '>', 0)
                ->where(function ($q) {
                    $q->where('total_price', '<=', 0)
                        ->orWhereDoesntHave('products');
                }),
            'partial_return' => $query->where('total_return', '>', 0)
                ->where('total_price', '>', 0)
                ->whereHas('products'),
            'paid' => $query->where('total_return', '<=', 0)
                ->where('remaining', '<=', 0),
            'partial' => $query->where('total_return', '<=', 0)
                ->where('remaining', '>', 0)
                ->where(function ($q) {
                    $q->where('paid_at_sale', '>', 0)
                        ->orWhereHas('payments');
                }),
            'unpaid' => $query->where('total_return', '<=', 0)
                ->where('remaining', '>', 0)
                ->where('paid_at_sale', '<=', 0)
                ->whereDoesntHave('payments', function ($q) {
                    $q->where('amount', '>', 0);
                }),
            default => null,
        };
    }
}

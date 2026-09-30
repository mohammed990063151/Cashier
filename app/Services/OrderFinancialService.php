<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Support\DecimalMath;
use App\Support\SaleUnits;

class OrderFinancialService
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Order $order): array
    {
        $order->loadMissing(['products', 'payments', 'returns']);

        $isOpening = (bool) ($order->is_opening_balance ?? false);
        $totalSale = 0.0;
        if ($isOpening) {
            $totalSale = round((float) ($order->total_price ?? 0), 2);
        } else {
            foreach ($order->products as $product) {
                $totalSale += SaleUnits::lineMoney($product);
            }
            $totalSale = round($totalSale, 2);
        }

        $paidAtSale = round((float) ($order->paid_at_sale ?? 0), 2);
        $invoiceDiscount = round((float) ($order->invoice_discount ?? 0), 2);

        if ($paidAtSale <= 0 && $invoiceDiscount > 0 && $invoiceDiscount >= $totalSale) {
            $paidAtSale = $invoiceDiscount;
            $invoiceDiscount = 0;
        }

        $totalAfterDiscount = round(max($totalSale - $invoiceDiscount, 0), 2);
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

        // المسترد نقداً لا يُحذف من جدول الدفعات — نخصمه لصافي المدفوع والمتبقي
        $totalRefundedToCustomer = round((float) $order->returns->sum('refund_amount'), 2);
        $totalPaid = round($totalPaid, 2);
        $netPaid = round(max(0, $totalPaid - $totalRefundedToCustomer), 2);
        $remaining = $order->written_off_at
            ? 0.0
            : round(max($totalAfterDiscount - $netPaid, 0), 2);

        $totalPurchase = $isOpening ? 0.0 : $order->products->sum(
            fn ($product) => $product->pivot->quantity * $product->pivot->cost_price
        );
        $profitBeforeDiscount = $isOpening ? 0.0 : ($totalSale - $totalPurchase);
        $profitAfterDiscount = $isOpening ? 0.0 : ($totalAfterDiscount - $totalPurchase);
        $profitPercentage = (! $isOpening && $totalPurchase > 0)
            ? ($profitAfterDiscount / $totalPurchase) * 100
            : 0;

        $returnMetrics = $this->returnMetrics(
            $order,
            $totalSale,
            $invoiceDiscount,
            $totalAfterDiscount,
            $totalPaid,
            $netPaid,
            $totalRefundedToCustomer
        );

        return array_merge(compact(
            'order',
            'totalSale',
            'invoiceDiscount',
            'totalAfterDiscount',
            'paidAtSale',
            'paymentsSum',
            'totalPaid',
            'netPaid',
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
        float $totalPaid,
        float $netPaid,
        float $totalRefundedToCustomer
    ): array {
        $totalReturnedMerchandise = round((float) ($order->total_return ?? 0), 2);
        $originalTotalSale = round($totalSale + $totalReturnedMerchandise, 2);
        $originalTotalAfterDiscount = max($originalTotalSale - $invoiceDiscount, 0);
        $hasReturns = $totalReturnedMerchandise > 0.009;
        $isFullyReturned = $hasReturns && ($totalSale <= 0.009 || $order->products->isEmpty());
        $overpaidAfterReturn = round(max(0, $netPaid - $totalAfterDiscount), 2);

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
    public function formatProductSaleLine($product, ?float $frozenRate = null): array
    {
        $pieces = (float) $product->pivot->quantity;
        $piecePrice = (float) $product->pivot->sale_price;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $mode = SaleUnits::normalizeSaleMode($product->sale_mode ?? null);
        $measure = SaleUnits::normalizeMeasureUnit($product->measure_unit ?? null);
        $lineTotal = SaleUnits::lineMoney($product);
        $savedLines = SaleUnits::unitLinesFromPivot($product);

        if ($savedLines !== []) {
            return [
                'quantity' => SaleUnits::formatUnitLines($savedLines),
                'price' => SaleUnits::formatUnitPrices($savedLines),
                'line_total' => $lineTotal,
            ];
        }

        $quantityText = SaleUnits::formatQuantityLabel($pieces, $bulkSize, $mode, $measure);

        $currency = app(CurrencyService::class);
        $usdOf = fn (float $amount): string => $currency->annotate($amount, $frozenRate);

        if ($measure === SaleUnits::UNIT_KILO) {
            $unitPrice = (float) $piecePrice;
            $priceText = DecimalMath::display($unitPrice).' ج.س'.$usdOf($unitPrice).' / كيلو';
        } elseif (
            ($mode === SaleUnits::MODE_BULK_ONLY || $measure === SaleUnits::UNIT_CARTON)
            && $bulkSize > 1
        ) {
            $cartons = $bulkSize > 0 ? $pieces / $bulkSize : 0;
            $unitPrice = $cartons > 0 ? round($lineTotal / $cartons, 2) : round($piecePrice * $bulkSize, 2);
            $priceText = DecimalMath::display($unitPrice).' ج.س'.$usdOf($unitPrice).' / كرتونة';
        } else {
            $unitPrice = round($piecePrice, 2);
            $priceText = DecimalMath::display($unitPrice).' ج.س'.$usdOf($unitPrice).' / حبة';
        }

        return [
            'quantity' => $quantityText,
            'price' => $priceText,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * @return array<int, array{label: string, count: float|int, pieces: float|int, piece_price: float, line_total: float}>
     */
    public function productUnitBreakdown($product): array
    {
        $pieces = (float) $product->pivot->quantity;
        $bulkSize = max(1, (int) ($product->pieces_per_carton ?? 12));
        $savedLines = SaleUnits::unitLinesFromPivot($product);
        if ($savedLines !== []) {
            $labels = ['bulk' => 'كرتونة', 'piece' => 'حبة', 'kilo' => 'كيلو'];
            $out = [];
            foreach ($labels as $key => $label) {
                $qty = (float) ($savedLines[$key]['qty'] ?? 0);
                $price = (float) ($savedLines[$key]['price'] ?? 0);
                if ($qty <= 0.0005) {
                    continue;
                }
                $multiplier = SaleUnits::multiplier($key, $bulkSize);
                $out[] = [
                    'label' => $label,
                    'count' => $qty,
                    'pieces' => $qty * $multiplier,
                    'piece_price' => $multiplier > 0 ? ($price / $multiplier) : $price,
                    'unit_price' => $price,
                    'line_total' => round($qty * $price, 2),
                ];
            }

            return $out;
        }

        $lineMoney = SaleUnits::lineMoney($product);
        $lines = SaleUnits::breakdownLines(
            $pieces,
            $bulkSize,
            $product->sale_mode ?? null,
            $product->measure_unit ?? null
        );

        return array_map(function ($line) use ($lineMoney, $pieces) {
            $share = $pieces > 0 ? ($lineMoney * ((float) $line['pieces'] / $pieces)) : 0;
            $lineTotal = round($share, 2);
            $count = max((float) $line['count'], 0.0001);
            $unitPrice = round($lineTotal / $count, 2);

            return array_merge($line, [
                'piece_price' => $pieces > 0 ? ($lineMoney / $pieces) : 0,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }, $lines);
    }

    public function paymentStatus(Order $order): string
    {
        if ($order->written_off_at) {
            return 'written_off';
        }

        $data = $this->calculate($order);

        if ($data['hasReturns']) {
            return $data['isFullyReturned'] ? 'returned' : 'partial_return';
        }

        $remaining = (float) $data['remaining'];
        $totalAfterDiscount = (float) $data['totalAfterDiscount'];
        $netPaid = (float) ($data['netPaid'] ?? $data['totalPaid']);

        if ($totalAfterDiscount <= 0.009 || $remaining <= 0.009) {
            return 'paid';
        }

        if ($netPaid > 0.009) {
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
            'written_off' => 'دين معدوم',
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
            'written_off' => 'label-default',
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
            'paid' => $query->whereNull('written_off_at')
                ->where('total_return', '<=', 0)
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

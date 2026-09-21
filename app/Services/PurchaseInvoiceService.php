<?php

namespace App\Services;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Support\PurchaseLineNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseInvoiceService
{
    public function __construct(
        protected CashService $cashService,
        protected SupplierPaymentScheduleService $supplierSchedule
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array{lines: Collection, total: float}
     */
    public function buildLines(array $rawItems): array
    {
        $lines = collect();
        $total = 0.0;
        $seenProducts = [];

        foreach ($rawItems as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId < 1) {
                throw new InvalidArgumentException('اختر المنتج في السطر '.($index + 1).'.');
            }

            if (isset($seenProducts[$productId])) {
                throw new InvalidArgumentException('المنتج مكرر في أكثر من سطر — ادمج الكمية في سطر واحد.');
            }

            $seenProducts[$productId] = true;
            $product = Product::findOrFail($productId);
            $normalized = PurchaseLineNormalizer::normalize($item, $product);

            if ($normalized['subtotal'] <= 0) {
                throw new InvalidArgumentException('سعر السطر '.($index + 1).' يجب أن يكون أكبر من صفر.');
            }

            $lines->push(array_merge($normalized, ['product' => $product]));
            $total += $normalized['subtotal'];
        }

        if ($lines->isEmpty()) {
            throw new InvalidArgumentException('أضف منتجاً واحداً على الأقل.');
        }

        return ['lines' => $lines, 'total' => round($total, 2)];
    }

    public function nextInvoiceNumber(): string
    {
        $lastInvoice = PurchaseInvoice::orderByDesc('id')->first();
        if ($lastInvoice && preg_match('/RQI-(\d{5})/', $lastInvoice->invoice_number, $matches)) {
            $newNumber = str_pad((int) $matches[1] + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return 'RQI-'.$newNumber;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $rawItems
     * @param  array<int, array<string, mixed>>|null  $installmentRows
     */
    public function create(array $data, array $rawItems, ?array $installmentRows = null): PurchaseInvoice
    {
        $paid = round((float) ($data['paid'] ?? 0), 2);
        $built = $this->buildLines($rawItems);
        $total = $built['total'];

        if ($paid > $total + 0.02) {
            throw new InvalidArgumentException('المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة.');
        }

        if ($paid > 0 && $this->cashService->getBalance() < $paid) {
            throw new InvalidArgumentException('رصيد الخزينة غير كافٍ للمبلغ المدفوع.');
        }

        $remaining = max($total - $paid, 0);

        return DB::transaction(function () use ($data, $built, $paid, $total, $remaining, $installmentRows) {
            $invoice = PurchaseInvoice::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'supplier_id' => $data['supplier_id'],
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'payment_due_at' => $data['payment_due_at'] ?? null,
                'payment_notes' => $data['payment_notes'] ?? null,
            ]);

            foreach ($built['lines'] as $line) {
                $this->applyLineToStock($invoice, $line);
            }

            $this->syncSupplierBalance($invoice->supplier_id);

            if ($paid > 0) {
                $this->recordSupplierPayment($invoice, $paid, 'دفعة عند إنشاء فاتورة الشراء');
                $this->cashService->record(
                    'deduct',
                    $paid,
                    "سداد فاتورة شراء {$invoice->invoice_number} — المبلغ: ".number_format($paid, 2).' ج.س',
                    'purchase',
                    now(),
                    null,
                    null,
                    $invoice->id
                );
            }

            if ($remaining > 0.009 && $installmentRows) {
                $this->supplierSchedule->saveInstallments($invoice->fresh(), $installmentRows);
            } elseif ($remaining <= 0.009) {
                $invoice->update(['payment_due_at' => null]);
                $invoice->paymentInstallments()->whereNull('paid_at')->delete();
            }

            return $invoice->fresh(['items.product', 'supplier', 'paymentInstallments']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $rawItems
     */
    public function update(PurchaseInvoice $invoice, array $data, array $rawItems): PurchaseInvoice
    {
        $built = $this->buildLines($rawItems);
        $total = $built['total'];
        $oldPaid = (float) $invoice->paid;
        $paid = round((float) ($data['paid'] ?? $oldPaid), 2);
        $difference = $paid - $oldPaid;

        if ($paid > $total + 0.02) {
            throw new InvalidArgumentException('المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة.');
        }

        if ($difference > 0 && $this->cashService->getBalance() < $difference) {
            throw new InvalidArgumentException('رصيد الخزينة غير كافٍ لزيادة المدفوع.');
        }

        $remaining = max($total - $paid, 0);

        return DB::transaction(function () use ($invoice, $data, $built, $paid, $total, $remaining, $difference) {
            foreach ($invoice->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->update([
                        'stock' => max(0, \App\Support\DecimalMath::sub((float) $product->stock, (float) $oldItem->quantity)),
                    ]);
                }
            }
            $invoice->items()->delete();

            $invoice->update([
                'supplier_id' => $data['supplier_id'],
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'total' => $total,
                'paid' => $paid,
                'remaining' => $remaining,
                'payment_due_at' => $data['payment_due_at'] ?? $invoice->payment_due_at,
                'payment_notes' => $data['payment_notes'] ?? $invoice->payment_notes,
            ]);

            foreach ($built['lines'] as $line) {
                $this->applyLineToStock($invoice, $line);
            }

            $this->syncSupplierBalance($invoice->supplier_id);

            if ($difference != 0) {
                $type = $difference > 0 ? 'deduct' : 'add';
                $this->cashService->record(
                    $type,
                    abs($difference),
                    "تعديل سداد فاتورة شراء {$invoice->invoice_number} — الفرق: ".number_format(abs($difference), 2).' ج.س',
                    'purchase',
                    now(),
                    null,
                    null,
                    $invoice->id
                );
            }

            if ($remaining <= 0.009) {
                $invoice->update(['payment_due_at' => null]);
                $invoice->paymentInstallments()->whereNull('paid_at')->delete();
            }

            return $invoice->fresh(['items.product', 'supplier', 'paymentInstallments']);
        });
    }

    /**
     * @param  array<string, mixed>  $line
     */
    protected function applyLineToStock(PurchaseInvoice $invoice, array $line): void
    {
        /** @var Product $product */
        $product = $line['product'];
        $pieces = \App\Support\DecimalMath::round($line['quantity']);
        $subtotal = \App\Support\DecimalMath::round($line['subtotal']);
        $pricePerPiece = $pieces > 0 ? \App\Support\DecimalMath::div($subtotal, $pieces) : 0;

        $invoice->items()->create([
            'product_id' => $product->id,
            'purchase_unit' => $line['purchase_unit'],
            'entered_qty' => \App\Support\DecimalMath::round($line['entered_qty']),
            'quantity' => $pieces,
            'price' => \App\Support\DecimalMath::round($line['price']),
            'subtotal' => $subtotal,
        ]);

        $oldQuantity = \App\Support\DecimalMath::round($product->stock);
        $oldPrice = (float) ($product->purchase_price ?? 0);
        $totalQuantity = \App\Support\DecimalMath::add($oldQuantity, $pieces);
        $avgPrice = $totalQuantity > 0
            ? \App\Support\DecimalMath::round((($oldQuantity * $oldPrice) + ($pieces * $pricePerPiece)) / $totalQuantity)
            : $pricePerPiece;

        if (abs($avgPrice - $oldPrice) > 0.0005) {
            PriceHistory::create([
                'product_id' => $product->id,
                'old_price' => $oldPrice,
                'new_price' => $avgPrice,
                'type' => 'purchase_invoice',
            ]);
        }

        $product->update([
            'stock' => $totalQuantity,
            'purchase_price' => $avgPrice,
        ]);
    }

    protected function syncSupplierBalance(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $totalRemaining = (float) PurchaseInvoice::where('supplier_id', $supplierId)->sum('remaining');
        $supplier->update(['balance' => $totalRemaining]);
    }

    protected function recordSupplierPayment(PurchaseInvoice $invoice, float $amount, string $note): void
    {
        SupplierPayment::create([
            'supplier_id' => $invoice->supplier_id,
            'purchase_invoice_id' => $invoice->id,
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'note' => $note,
        ]);
    }
}

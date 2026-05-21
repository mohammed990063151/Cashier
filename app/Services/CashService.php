<?php

namespace App\Services;

use App\Models\Cash;
use App\Models\CashSetting;
use App\Models\CashTransaction;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class CashService
{
    /**
     * سجل الخزينة الوحيد (id = 1) مع دمج أي سجلات قديمة مكررة.
     */
    protected function cashAccount(): Cash
    {
        $primary = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);

        $others = Cash::where('id', '!=', 1)->get();
        if ($others->isNotEmpty()) {
            $primary->balance = (float) $primary->balance + $others->sum('balance');
            $primary->save();
            Cash::where('id', '!=', 1)->delete();
        }

        return $primary;
    }

    /**
     * @param  string  $type  add|deduct|in|out
     */
    public function record(
        string $type,
        float $amount,
        ?string $description = null,
        ?string $category = null,
        $date = null,
        $orderId = null,
        $paymentId = null,
        $purchaseInvoiceId = null,
        $expenseId = null,
        $supplierPaymentId = null,
        $orderReturnId = null
    ): CashTransaction {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'المبلغ يجب أن يكون أكبر من صفر.',
            ]);
        }

        $dbType = in_array($type, ['add', 'in'], true) ? 'add' : 'deduct';

        if (! $this->isCategoryEnabled($category, $dbType)) {
            throw ValidationException::withMessages([
                'amount' => 'هذا النوع من الحركات معطّل في إعدادات الخزينة.',
            ]);
        }

        $cash = $this->cashAccount();

        if ($dbType === 'deduct' && (float) $cash->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => "عذرًا، لا يوجد رصيد كافٍ. الرصيد المتوفر: {$cash->balance}",
            ]);
        }

        if ($dbType === 'add') {
            $cash->balance += $amount;
        } else {
            $cash->balance -= $amount;
        }

        $cash->save();

        return CashTransaction::create([
            'type' => $dbType,
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $date ?? now(),
            'category' => $category,
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'purchase_invoice_id' => $purchaseInvoiceId,
            'expense_id' => $expenseId,
            'supplier_payment_id' => $supplierPaymentId,
            'order_return_id' => $orderReturnId,
        ]);
    }

    public static function orderPaymentDescription(Order $order, ?float $amount = null): string
    {
        $client = $order->relationLoaded('client') ? $order->client->name : ($order->client()->value('name') ?? 'العميل');
        $base = "تحصيل — طلب رقم {$order->order_number} — العميل: {$client}";

        return $amount !== null
            ? $base.' — المبلغ: '.number_format($amount, 2).' ج.س'
            : $base;
    }

    public static function orderSalePaymentDescription(Order $order, float $amount): string
    {
        $client = $order->relationLoaded('client') ? $order->client->name : ($order->client()->value('name') ?? 'العميل');

        return "دفع عند البيع — طلب رقم {$order->order_number} — العميل: {$client} — المبلغ: "
            .number_format($amount, 2).' ج.س';
    }

    public static function orderReturnDescription(
        Order $order,
        float $amount,
        string $itemsSummary,
        string $returnNumber
    ): string {
        $client = $order->relationLoaded('client') ? $order->client->name : ($order->client()->value('name') ?? 'العميل');

        return "مرتجع — {$returnNumber} — طلب رقم {$order->order_number} — إرجاع "
            .number_format($amount, 2)." ج.س للعميل {$client}"
            .($itemsSummary ? " — ({$itemsSummary})" : '');
    }

    public function updateTransaction(
        CashTransaction $transaction,
        float $newAmount,
        ?string $description = null,
        ?string $category = null,
        $date = null
    ): CashTransaction {
        $newAmount = round($newAmount, 2);
        $cash = $this->cashAccount();

        if ($transaction->type === 'add') {
            $cash->balance -= (float) $transaction->amount;
        } else {
            $cash->balance += (float) $transaction->amount;
        }

        if ($transaction->type === 'add') {
            $cash->balance += $newAmount;
        } else {
            if ((float) $cash->balance < $newAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'الرصيد غير كافٍ لتعديل العملية.',
                ]);
            }
            $cash->balance -= $newAmount;
        }

        $cash->save();

        $transaction->update([
            'amount' => $newAmount,
            'description' => $description,
            'transaction_date' => $date ?? $transaction->transaction_date,
            'category' => $category ?? $transaction->category,
        ]);

        return $transaction;
    }

    public function deleteTransaction(CashTransaction $transaction): void
    {
        $cash = $this->cashAccount();

        if ($transaction->type === 'add') {
            $cash->balance -= (float) $transaction->amount;
        } else {
            $cash->balance += (float) $transaction->amount;
        }

        $cash->save();
        $transaction->delete();
    }

    public function deleteTransactionsForOrder(Order $order): void
    {
        $paymentIds = $order->payments()->pluck('id');

        $transactions = CashTransaction::query()
            ->where('order_id', $order->id)
            ->when($paymentIds->isNotEmpty(), fn ($q) => $q->orWhereIn('payment_id', $paymentIds))
            ->get();

        foreach ($transactions as $transaction) {
            $this->deleteTransaction($transaction);
        }
    }

    public function getBalance(): float
    {
        return (float) $this->cashAccount()->balance;
    }

    protected function isCategoryEnabled(?string $category, string $dbType): bool
    {
        if ($category === null || $category === 'direct') {
            return true;
        }

        $settings = CashSetting::firstOrCreate(['id' => 1], [
            'add_sales' => true,
            'add_client_payments' => true,
            'deduct_purchases' => true,
            'deduct_supplier_payments' => true,
            'deduct_expenses' => true,
        ]);

        if ($dbType === 'add') {
            return match ($category) {
                'order' => (bool) $settings->add_sales,
                'payment' => (bool) $settings->add_client_payments,
                default => true,
            };
        }

        return match ($category) {
            'purchase' => (bool) $settings->deduct_purchases,
            'supplier_payment' => (bool) $settings->deduct_supplier_payments,
            'operational', 'other' => (bool) $settings->deduct_expenses,
            default => true,
        };
    }
}

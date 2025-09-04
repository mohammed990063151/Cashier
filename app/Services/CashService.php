<?php

namespace App\Services;

use App\Models\Cash;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
class CashService
{
    /**
     * تسجيل حركة مالية جديدة
     *
     * @param string $type "add", "deduct", "in", "out"
     * @param float $amount
     * @param string|null $description
     * @param string|null $category
     * @param string|null $date
     * @return CashTransaction
     * @throws Exception
     */

    public function record(string $type, float $amount, ?string $description = null, ?string $category = null, $date = null ,$orderId = null ,$paymentId = null ,$purchaseInvoiceId=null ,$ExpenseId = NULL): CashTransaction
{
    // الحصول على الرصيد الكلي الموجود في الخزينة
    $totalBalance = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);

    // إذا لم يوجد أي رصيد، يمكن إنشاء سجل جديد
    if ($totalBalance->balance <= 0 && $type === 'deduct') {
        throw ValidationException::withMessages([
        'amount' => "عذرًا، لا يوجد رصيد كافٍ لتعديل العملية. الرصيد المتوفر: {$totalBalance->balance}"
    ]);
    }

    // إذا لم يوجد سجل أصلاً، ننشئ واحد
    $cash = Cash::firstOrCreate([], ['balance' => $totalBalance]);

    // تحويل النوع لقيم الجدول
    $dbType = in_array($type, ['add', 'in']) ? 'add' : 'deduct';

    // تعديل الرصيد
    if ($dbType === 'add') {
        $cash->balance += $amount;
    } elseif ($dbType === 'deduct') {
        if ($totalBalance->balance < $amount) {
            throw ValidationException::withMessages([
        'amount' => "عذرًا، لا يوجد رصيد كافٍ لتعديل العملية. الرصيد المتوفر: {$totalBalance->balance}"
 ]);
    }
        $cash->balance -= $amount;
    }

    $cash->save();

    return CashTransaction::create([
        'type'             => $dbType,
        'amount'           => $amount,
        'description'      => $description,
        'transaction_date' => $date ?? \Carbon\Carbon::now(),
        'category'         => $category,
        'order_id'         => $orderId,
        'payment_id'       => $paymentId,
        'purchase_invoice_id'   => $purchaseInvoiceId,
        'expense_id'   => $ExpenseId,
    ]);
}


    /**
     * تعديل حركة مالية موجودة (يتم استرجاع أثرها أولاً)
     *
     * @param CashTransaction $transaction
     * @param float $newAmount
     * @param string|null $description
     * @param string|null $category
     * @param string|null $date
     * @return CashTransaction
     * @throws Exception
     */
    public function updateTransaction(CashTransaction $transaction, float $newAmount, ?string $description = null, ?string $category = null, $date = null): CashTransaction
    {
        $cash = Cash::first();

        // استرجاع الرصيد السابق
        if ($transaction->type === 'add') {
            $cash->balance -= $transaction->amount;
        } else {
            $cash->balance += $transaction->amount;
        }

        // تطبيق الرصيد الجديد
        if ($transaction->type === 'add') {
            $cash->balance += $newAmount;
        } else {
            if ($cash->balance < $newAmount) {
                throw new Exception("الرصيد غير كافٍ لتعديل العملية.");
            }
            $cash->balance -= $newAmount;
        }

        $cash->save();

        $transaction->update([
            'amount'           => $newAmount,
            'description'      => $description,
            'transaction_date' => $date ?? $transaction->transaction_date,
            'category'         => $category,
        ]);

        return $transaction;
    }

    /**
     * حذف حركة مالية واسترجاع أثرها على الرصيد
     *
     * @param CashTransaction $transaction
     * @return void
     */
    public function deleteTransaction(CashTransaction $transaction): void
    {
        $cash = Cash::first();

        if ($transaction->type === 'add') {
            $cash->balance -= $transaction->amount;
        } else {
            $cash->balance += $transaction->amount;
        }

        $cash->save();
        $transaction->delete();
    }

    /**
 * الحصول على رصيد الخزينة الحالي
 *
 * @return float
 */
public function getBalance(): float
{
    $cash = Cash::firstOrCreate(['id' => 1], ['balance' => 0]);
    return $cash->balance;
}

}

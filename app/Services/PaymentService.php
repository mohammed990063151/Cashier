<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected OrderFinancialService $orderFinancial,
        protected CollectionScheduleService $collectionSchedule,
        protected CashService $cashService
    ) {}

    public function maxAllowedAmount(Order $order, ?Payment $excluding = null): float
    {
        $data = $this->orderFinancial->calculate($order);
        $excluded = $excluding ? (float) $excluding->amount : 0;

        return round($data['remaining'] + $excluded, 2);
    }

    /**
     * @param  array{amount: float, method?: string, notes?: string|null}  $data
     * @param  array<int, UploadedFile>  $newReceipts
     */
    public function updatePayment(Payment $payment, array $data, array $newReceipts = []): Order
    {
        if ($payment->method === 'cash_at_sale') {
            throw new \InvalidArgumentException('دفعة عند إنشاء الطلب تُعدّل من صفحة تعديل الطلب.');
        }

        return DB::transaction(function () use ($payment, $data, $newReceipts) {
            $payment->load('receipts');
            $order = $payment->order()->with(['products', 'payments.receipts', 'paymentInstallments'])->firstOrFail();
            $newAmount = round((float) $data['amount'], 2);
            $newMethod = $data['method'] ?? $payment->method;
            $maxAllowed = $this->maxAllowedAmount($order, $payment);

            if ($newAmount > $maxAllowed + 0.02) {
                throw new \InvalidArgumentException("المبلغ أكبر من المسموح ({$maxAllowed} ج.س).");
            }

            if ($newMethod === 'bank' && ! $payment->hasBankReceipts() && $newReceipts === []) {
                throw new \InvalidArgumentException('صورة إشعار التحويل البنكي مطلوبة للتحويل البنكي.');
            }

            $transaction = $payment->transaction;

            if ($transaction) {
                $this->cashService->updateTransaction(
                    $transaction,
                    $newAmount,
                    CashService::orderPaymentDescription($order, $newAmount).' — تعديل',
                    'payment',
                    $payment->created_at
                );
            } else {
                $this->cashService->record(
                    'add',
                    $newAmount,
                    CashService::orderPaymentDescription($order, $newAmount),
                    'payment',
                    $payment->created_at,
                    $order->id,
                    $payment->id
                );
            }

            $fields = [
                'amount' => $newAmount,
                'notes' => $data['notes'] ?? $payment->notes,
                'method' => $newMethod,
            ];

            if ($newMethod !== 'bank') {
                $this->deleteAllReceipts($payment);
                $fields['bank_receipt'] = null;
            } else {
                if ($newReceipts !== []) {
                    $this->attachReceiptFiles($payment, $newReceipts);
                }
            }

            $payment->update($fields);

            $order = $this->orderFinancial->syncOrderTotals($order->fresh(['products', 'payments.receipts', 'paymentInstallments']));
            $this->collectionSchedule->resyncInstallmentsFromPayments($order);

            return $order->fresh(['products', 'payments.receipts', 'paymentInstallments', 'client']);
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function attachReceiptFiles(Payment $payment, array $files): void
    {
        $sort = (int) $payment->receipts()->max('sort_order') + 1;
        $legacyFilename = null;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $filename = $this->storeBankReceipt($file);
            PaymentReceipt::create([
                'payment_id' => $payment->id,
                'filename' => $filename,
                'sort_order' => $sort++,
            ]);
            $legacyFilename ??= $filename;
        }

        if ($legacyFilename && ! $payment->bank_receipt) {
            $payment->update(['bank_receipt' => $legacyFilename]);
        }
    }

    public function deletePayment(Payment $payment): Order
    {
        if ($payment->method === 'cash_at_sale') {
            throw new \InvalidArgumentException('لا يمكن حذف دفعة إنشاء الطلب من هنا.');
        }

        return DB::transaction(function () use ($payment) {
            $order = $payment->order()->with(['products', 'payments.receipts', 'paymentInstallments'])->firstOrFail();

            if ($transaction = $payment->transaction) {
                $this->cashService->deleteTransaction($transaction);
            }

            $this->deleteAllReceipts($payment);
            $payment->delete();

            $order = $this->orderFinancial->syncOrderTotals($order->fresh(['products', 'payments.receipts', 'paymentInstallments']));
            $this->collectionSchedule->resyncInstallmentsFromPayments($order);

            return $order->fresh(['products', 'payments.receipts', 'paymentInstallments', 'client']);
        });
    }

    public function deleteAllReceipts(Payment $payment): void
    {
        $payment->load('receipts');

        foreach ($payment->receipts as $receipt) {
            $this->deleteBankReceiptFile($receipt->filename);
            $receipt->delete();
        }

        $this->deleteBankReceiptFile($payment->bank_receipt);
    }

    protected function storeBankReceipt(UploadedFile $file): string
    {
        $dir = public_path('uploads/payment_receipts');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $file->hashName();
        $file->move($dir, $filename);

        return $filename;
    }

    protected function deleteBankReceiptFile(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/payment_receipts/'.$filename);
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('supplier_payments') || Schema::hasColumn('supplier_payments', 'usd_rate')) {
            return;
        }

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->decimal('usd_rate', 15, 2)->nullable()->after('amount');
        });

        $fallback = null;
        if (Schema::hasTable('exchange_rates')) {
            $fallback = DB::table('exchange_rates')->orderByDesc('rate_date')->orderByDesc('id')->value('sdg_per_usd');
        }

        $payments = DB::table('supplier_payments')->whereNull('usd_rate')->get(['id', 'purchase_invoice_id']);
        foreach ($payments as $payment) {
            $rate = null;
            if ($payment->purchase_invoice_id && Schema::hasColumn('purchase_invoices', 'usd_rate')) {
                $rate = DB::table('purchase_invoices')->where('id', $payment->purchase_invoice_id)->value('usd_rate');
            }
            $rate = (float) ($rate ?: $fallback);
            if ($rate > 0) {
                DB::table('supplier_payments')->where('id', $payment->id)->update(['usd_rate' => $rate]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('supplier_payments') && Schema::hasColumn('supplier_payments', 'usd_rate')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                $table->dropColumn('usd_rate');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_transactions', 'supplier_payment_id')) {
                $table->unsignedInteger('supplier_payment_id')->nullable()->after('purchase_invoice_id');
                $table->foreign('supplier_payment_id')
                    ->references('id')
                    ->on('supplier_payments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_transactions', 'supplier_payment_id')) {
                $table->dropForeign(['supplier_payment_id']);
                $table->dropColumn('supplier_payment_id');
            }
        });
    }
};

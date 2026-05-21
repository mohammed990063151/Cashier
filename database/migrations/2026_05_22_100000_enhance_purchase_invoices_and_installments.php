<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_invoices', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('invoice_number');
            }
            if (! Schema::hasColumn('purchase_invoices', 'payment_due_at')) {
                $table->date('payment_due_at')->nullable()->after('remaining');
            }
            if (! Schema::hasColumn('purchase_invoices', 'payment_notes')) {
                $table->text('payment_notes')->nullable()->after('payment_due_at');
            }
        });

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_invoice_items', 'purchase_unit')) {
                $table->string('purchase_unit', 20)->default('piece')->after('product_id');
            }
            if (! Schema::hasColumn('purchase_invoice_items', 'entered_qty')) {
                $table->unsignedInteger('entered_qty')->default(1)->after('purchase_unit');
            }
        });

        Schema::create('supplier_payment_installments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_invoice_id');
            $table->decimal('amount', 12, 2);
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('purchase_invoice_id')
                ->references('id')
                ->on('purchase_invoices')
                ->onDelete('cascade');

            $table->index(['due_at', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_installments');

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoice_items', 'entered_qty')) {
                $table->dropColumn('entered_qty');
            }
            if (Schema::hasColumn('purchase_invoice_items', 'purchase_unit')) {
                $table->dropColumn('purchase_unit');
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'payment_notes')) {
                $table->dropColumn('payment_notes');
            }
            if (Schema::hasColumn('purchase_invoices', 'payment_due_at')) {
                $table->dropColumn('payment_due_at');
            }
        });
    }
};

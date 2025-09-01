<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
          

                    $table->unsignedInteger('purchase_invoice_id')->nullable()->after('supplier_id');
    $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            // أولًا نحذف الـ foreign key
            $table->dropForeign(['purchase_invoice_id']);
            
            // ثم نحذف العمود
            $table->dropColumn('purchase_invoice_id');
        });
    }
};

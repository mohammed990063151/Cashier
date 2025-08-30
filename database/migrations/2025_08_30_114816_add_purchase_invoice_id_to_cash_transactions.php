<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::table('cash_transactions', function (Blueprint $table) {
    $table->unsignedInteger('purchase_invoice_id')->nullable()->after('order_id');
    $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('set null');
});

    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['purchase_invoice_id']);
            $table->dropColumn('purchase_invoice_id');
        });
    }
};

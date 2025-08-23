<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaidAmountAndInvoiceDateToPurchaseInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::table('purchase_invoices', function ($table) {
    if (!Schema::hasColumn('purchase_invoices', 'paid_amount')) {
        $table->decimal('paid_amount', 15, 2)->default(0);
    }
    if (!Schema::hasColumn('purchase_invoices', 'invoice_date')) {
        $table->date('invoice_date')->nullable();
    }
});
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            //
        });
    }
}

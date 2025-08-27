<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaidAmountToSaleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('sale_invoices', function($table) {
        $table->decimal('paid_amount', 15, 2)->default(0);
    });
}

public function down()
{
    Schema::table('sale_invoices', function($table) {
        $table->dropColumn('paid_amount');
    });
}

}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfitToSaleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('sale_invoices', function ($table) {
        $table->decimal('profit', 15, 2)->default(0);
    });
}

public function down()
{
    Schema::table('sale_invoices', function ($table) {
        $table->dropColumn('profit');
    });
}

}

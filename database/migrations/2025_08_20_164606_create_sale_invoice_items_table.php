<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
   Schema::create('sale_invoice_items', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('sale_invoice_id'); // << هنا
    $table->unsignedInteger('product_id');
    $table->integer('quantity');
    $table->decimal('price', 15, 2);
    $table->timestamps();

    $table->foreign('sale_invoice_id')->references('id')->on('sale_invoices')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_invoice_items');
    }
}

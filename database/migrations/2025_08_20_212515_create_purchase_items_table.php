<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
 Schema::create('purchase_items', function (Blueprint $table) {
    $table->increments('id'); // بدل $table->id();
    $table->unsignedInteger('invoice_id'); // بدل $table->foreignId()
    $table->unsignedInteger('product_id');
    $table->decimal('price', 15,2);
    $table->integer('quantity');
    $table->decimal('subtotal', 15,2);
    $table->timestamps();

    $table->foreign('invoice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}

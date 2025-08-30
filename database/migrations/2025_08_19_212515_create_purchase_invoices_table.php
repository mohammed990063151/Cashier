<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoicesTable  extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
   Schema::create('purchase_invoices', function (Blueprint $table) {
    $table->increments('id'); // بدل $table->id();
    $table->unsignedInteger('supplier_id'); // بدل $table->foreignId()
    $table->decimal('total', 15,2)->default(0);
    $table->decimal('paid', 15,2)->default(0);
    $table->decimal('remaining', 15,2)->default(0);
    $table->timestamps();

    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
    $table->softDeletes(); 
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoices');
    }
}

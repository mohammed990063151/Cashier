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
    Schema::create('purchase_invoice_items', function (Blueprint $table) {
        
    $table->increments('id');
    $table->unsignedInteger('purchase_invoice_id'); // << هنا
    $table->unsignedInteger('product_id');
    $table->integer('quantity');
    $table->decimal('price', 15, 2);
    $table->timestamps();

    $table->foreign('sale_invopurchase_invoice_idice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});
}

public function down(): void
{
    Schema::dropIfExists('purchase_invoice_items');
}

};

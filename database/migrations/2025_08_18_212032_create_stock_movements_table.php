<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
       Schema::create('stock_movements', function (Blueprint $table) {
    $table->increments('id'); // المفتاح الأساسي
    $table->unsignedInteger('product_id'); // متوافق مع products.id (increments)
    $table->integer('quantity');
    $table->enum('type', ['in','out']);
    $table->decimal('price', 15, 2)->default(0);
    $table->timestamps();

    // العلاقة مع جدول المنتجات
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});

    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('price_histories', function (Blueprint $table) {
    $table->id();
    // $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->decimal('old_price', 15, 2);
    $table->decimal('new_price', 15, 2);
    $table->string('type')->default('sale');
$table->unsignedInteger('product_id');
       $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');// sale or purchase
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};

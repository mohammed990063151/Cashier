<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payment_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->decimal('amount', 12, 2);
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index(['due_at', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_installments');
    }
};

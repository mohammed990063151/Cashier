<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->string('return_number', 32)->unique();
            $table->decimal('items_total', 15, 2);
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->decimal('remaining_reduced', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('return_date');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->cascadeOnDelete();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        if (! Schema::hasColumn('cash_transactions', 'order_return_id')) {
            Schema::table('cash_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('order_return_id')->nullable()->after('order_id');
                $table->foreign('order_return_id')
                    ->references('id')
                    ->on('order_returns')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_transactions', 'order_return_id')) {
                $table->dropForeign(['order_return_id']);
                $table->dropColumn('order_return_id');
            }
        });

        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');
    }
};

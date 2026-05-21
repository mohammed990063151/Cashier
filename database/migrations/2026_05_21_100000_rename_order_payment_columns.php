<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('discount', 'paid_at_sale');
            $table->renameColumn('tax_amount', 'invoice_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('paid_at_sale', 'discount');
            $table->renameColumn('invoice_discount', 'tax_amount');
        });
    }
};

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     Schema::create('cash_transactions', function (Blueprint $table) {
    //        $table->increments('id');
    //         $table->enum('type', ['in', 'out']); // in = دخل, out = مصروف
    //         $table->decimal('amount', 10, 2);
    //         $table->string('source')->nullable(); // مثال: بيع طلب رقم 1
    //         $table->timestamps();
    //     });
    // }

    public function up()
{
    Schema::create('cash_transactions', function (Blueprint $table) {
        $table->increments('id');
        $table->enum('type', ['add', 'deduct']); // نوع العملية
        $table->decimal('amount', 15, 2);
        $table->string('description')->nullable();
        $table->date('transaction_date');
        $table->string('category')->nullable(); // مثل فواتير بيع، شراء، مصروفات
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_transactions');
    }
}

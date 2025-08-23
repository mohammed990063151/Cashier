<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('cash_settings', function (Blueprint $table) {
        $table->increments('id'); 
        $table->boolean('add_sales')->default(true);
        $table->boolean('add_client_payments')->default(true);
        $table->boolean('deduct_purchases')->default(true);
        $table->boolean('deduct_supplier_payments')->default(true);
        $table->boolean('deduct_expenses')->default(true);
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
        Schema::dropIfExists('cash_settings');
    }
}

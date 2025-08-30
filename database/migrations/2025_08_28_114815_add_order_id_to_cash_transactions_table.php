<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('cash_transactions', function (Blueprint $table) {
        $table->unsignedInteger('order_id')->nullable()->after('id');
        $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('cash_transactions', function (Blueprint $table) {
        $table->dropForeign(['order_id']);
        $table->dropColumn('order_id');
    });
}


};

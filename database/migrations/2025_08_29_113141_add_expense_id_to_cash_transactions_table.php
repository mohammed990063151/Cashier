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
       Schema::table('cash_transactions', function (Blueprint $table) {
    $table->unsignedInteger('expense_id')->nullable()->after('order_id'); // نفس نوع id في payments

    $table->foreign('expense_id')
          ->references('id')
          ->on('expenses')
          ->onDelete('set null');
});

    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
            $table->dropColumn('expense_id');
        });
    }
};

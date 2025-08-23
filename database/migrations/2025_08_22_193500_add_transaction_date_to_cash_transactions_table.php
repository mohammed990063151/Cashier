<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionDateToCashTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            // إضافة عمود تاريخ المعاملة إذا لم يكن موجودًا
            if (!Schema::hasColumn('cash_transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_date');
        });
    }
}

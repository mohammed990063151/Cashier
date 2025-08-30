<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::table('cash_transactions', function (Blueprint $table) {
    $table->unsignedInteger('payment_id')->nullable()->after('order_id'); // نفس نوع id في payments

    $table->foreign('payment_id')
          ->references('id')
          ->on('payments')
          ->onDelete('set null');
});

    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};


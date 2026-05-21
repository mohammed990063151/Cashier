<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->string('filename');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onDelete('cascade');
        });

        if (Schema::hasColumn('payments', 'bank_receipt')) {
            foreach (DB::table('payments')
                ->whereNotNull('bank_receipt')
                ->where('bank_receipt', '!=', '')
                ->get() as $payment) {
                DB::table('payment_receipts')->insert([
                    'payment_id' => $payment->id,
                    'filename' => $payment->bank_receipt,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};

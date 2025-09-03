<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1️⃣ إضافة العمود بشكل مؤقت nullable
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('id');
        });

        // 2️⃣ تحديث السجلات القديمة برقم فاتورة تلقائي
        $invoices = DB::table('purchase_invoices')->get();
        foreach ($invoices as $invoice) {
            DB::table('purchase_invoices')
                ->where('id', $invoice->id)
                ->update(['invoice_number' => 'INV-' . $invoice->id]);
        }

        // 3️⃣ تعديل العمود ليصبح غير فارغ وفريد
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('invoice_number')->nullable(false)->unique()->change();
        });
    }

    public function down()
    {
        // التراجع عن التغييرات بحذف العمود
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};

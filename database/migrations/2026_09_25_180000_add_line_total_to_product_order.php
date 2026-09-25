<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_order', function (Blueprint $table) {
            if (! Schema::hasColumn('product_order', 'line_total')) {
                $table->decimal('line_total', 15, 3)->nullable()->after('sale_price');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            Schema::table('product_order', function (Blueprint $table) {
                $table->decimal('sale_price', 15, 6)->default(0)->change();
            });
        }

        // تعبئة line_total للأسطر القديمة من quantity × sale_price
        DB::table('product_order')
            ->whereNull('line_total')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $line = round((float) $row->quantity * (float) $row->sale_price, 3);
                    DB::table('product_order')->where('id', $row->id)->update([
                        'line_total' => $line,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('product_order', function (Blueprint $table) {
            if (Schema::hasColumn('product_order', 'line_total')) {
                $table->dropColumn('line_total');
            }
        });
    }
};

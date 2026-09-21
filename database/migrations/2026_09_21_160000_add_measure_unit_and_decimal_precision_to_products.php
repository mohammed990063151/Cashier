<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'measure_unit')) {
                $table->string('measure_unit', 20)->default('piece')->after('sale_mode');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: recreate-friendly alter via temporary table is heavy;
            // cast via new decimal columns then swap when needed.
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('purchase_price_tmp', 15, 3)->nullable();
                $table->decimal('sale_price_tmp', 15, 3)->nullable();
                $table->decimal('stock_tmp', 15, 3)->nullable();
            });

            DB::table('products')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('products')->where('id', $row->id)->update([
                        'purchase_price_tmp' => $row->purchase_price,
                        'sale_price_tmp' => $row->sale_price,
                        'stock_tmp' => $row->stock,
                    ]);
                }
            });

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['purchase_price', 'sale_price', 'stock']);
            });

            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('purchase_price_tmp', 'purchase_price');
                $table->renameColumn('sale_price_tmp', 'sale_price');
                $table->renameColumn('stock_tmp', 'stock');
            });
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('purchase_price', 15, 3)->default(0)->change();
                $table->decimal('sale_price', 15, 3)->default(0)->change();
                $table->decimal('stock', 15, 3)->default(0)->change();
            });
        }

        // Map existing bulk_only products to carton measure for clearer UX.
        DB::table('products')
            ->where('sale_mode', 'bulk_only')
            ->where(function ($q) {
                $q->whereNull('measure_unit')->orWhere('measure_unit', 'piece');
            })
            ->update(['measure_unit' => 'carton']);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'measure_unit')) {
                $table->dropColumn('measure_unit');
            }
        });
    }
};

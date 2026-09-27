<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sale_usd_rate')) {
                $table->decimal('sale_usd_rate', 15, 2)->nullable()->after('usd_rate');
            }
            if (! Schema::hasColumn('products', 'previous_sale_price')) {
                $table->decimal('previous_sale_price', 15, 3)->nullable()->after('sale_price');
            }
            if (! Schema::hasColumn('products', 'previous_sale_usd_rate')) {
                $table->decimal('previous_sale_usd_rate', 15, 2)->nullable()->after('previous_sale_price');
            }
        });

        if (Schema::hasColumn('products', 'usd_rate') && Schema::hasColumn('products', 'sale_usd_rate')) {
            DB::statement('UPDATE products SET sale_usd_rate = usd_rate WHERE sale_usd_rate IS NULL AND usd_rate IS NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            foreach (['previous_sale_usd_rate', 'previous_sale_price', 'sale_usd_rate'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

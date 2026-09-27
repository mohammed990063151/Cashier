<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'orders', 'purchase_invoices'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'usd_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->decimal('usd_rate', 15, 2)->nullable();
                });
            }
        }

        $rate = null;
        if (Schema::hasTable('exchange_rates')) {
            $rate = DB::table('exchange_rates')->orderByDesc('rate_date')->orderByDesc('id')->value('sdg_per_usd');
        }
        if (! $rate || (float) $rate <= 0) {
            return;
        }

        foreach (['products', 'orders', 'purchase_invoices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'usd_rate')) {
                DB::table($table)->whereNull('usd_rate')->update(['usd_rate' => $rate]);
            }
        }
    }

    public function down(): void
    {
        foreach (['products', 'orders', 'purchase_invoices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'usd_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('usd_rate');
                });
            }
        }
    }
};

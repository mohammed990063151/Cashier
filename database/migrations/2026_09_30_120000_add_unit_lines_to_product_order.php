<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_order') && ! Schema::hasColumn('product_order', 'unit_lines')) {
            Schema::table('product_order', function (Blueprint $table) {
                $table->text('unit_lines')->nullable()->after('line_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_order') && Schema::hasColumn('product_order', 'unit_lines')) {
            Schema::table('product_order', function (Blueprint $table) {
                $table->dropColumn('unit_lines');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('product_order', function (Blueprint $table) {
                $table->decimal('quantity_tmp', 15, 3)->nullable();
            });

            DB::table('product_order')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('product_order')->where('id', $row->id)->update([
                        'quantity_tmp' => $row->quantity,
                    ]);
                }
            });

            Schema::table('product_order', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });

            Schema::table('product_order', function (Blueprint $table) {
                $table->renameColumn('quantity_tmp', 'quantity');
            });
        } else {
            Schema::table('product_order', function (Blueprint $table) {
                $table->decimal('quantity', 15, 3)->default(1)->change();
            });
        }
    }

    public function down(): void
    {
        // Keep decimal precision; no-op downgrade.
    }
};

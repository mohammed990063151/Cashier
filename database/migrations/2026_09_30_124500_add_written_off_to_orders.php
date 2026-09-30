<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'written_off_at')) {
                $table->timestamp('written_off_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'written_off_amount')) {
                $table->decimal('written_off_amount', 15, 2)->nullable();
            }
            if (! Schema::hasColumn('orders', 'written_off_note')) {
                $table->string('written_off_note', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['written_off_note', 'written_off_amount', 'written_off_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

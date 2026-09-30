<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'is_opening_balance')) {
                $table->boolean('is_opening_balance')->default(false);
            }
            if (! Schema::hasColumn('orders', 'opening_reference')) {
                $table->string('opening_reference', 80)->nullable();
            }
            if (! Schema::hasColumn('orders', 'opening_details')) {
                $table->text('opening_details')->nullable();
            }
            if (! Schema::hasColumn('orders', 'opening_photo')) {
                $table->string('opening_photo')->nullable();
            }
            if (! Schema::hasColumn('orders', 'opening_date')) {
                $table->date('opening_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['opening_date', 'opening_photo', 'opening_details', 'opening_reference', 'is_opening_balance'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

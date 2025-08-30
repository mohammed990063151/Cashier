<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_return', 15, 2)->default(0)->after('total_price');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('total_return');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_return', 'tax_amount']);
        });
    }
};

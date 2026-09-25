<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('sdg_per_usd', 15, 2); // كم جنيه سوداني = دولار واحد
            $table->date('rate_date');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('rate_date');
        });

        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'usd_rate')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->decimal('usd_rate', 15, 2)->nullable()->after('linkedin'); // آخر سعر محفوظ
                $table->boolean('show_usd')->default(true)->after('usd_rate');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'usd_rate')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn(['usd_rate', 'show_usd']);
            });
        }
    }
};

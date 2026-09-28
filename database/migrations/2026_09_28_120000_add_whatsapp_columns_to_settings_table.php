<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_base_url')->nullable();
            $table->string('whatsapp_username')->nullable();
            $table->string('whatsapp_password')->nullable();
            $table->string('whatsapp_device_id')->nullable();
            $table->string('whatsapp_staff_phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'whatsapp_base_url',
                'whatsapp_username',
                'whatsapp_password',
                'whatsapp_device_id',
                'whatsapp_staff_phone',
            ]);
        });
    }
};

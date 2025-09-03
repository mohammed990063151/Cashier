<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();        // اسم الشركة
        $table->string('logo')->nullable();        // رابط اللوجو أو مسار الصورة
        $table->string('email')->nullable();       // البريد الإلكتروني
        $table->string('phone')->nullable();       // رقم الهاتف
        $table->string('address')->nullable();     // العنوان
        $table->string('facebook')->nullable();
        $table->string('twitter')->nullable();
        $table->string('instagram')->nullable();
        $table->string('linkedin')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المدينة
            $table->text('description')->nullable(); // وصف
            $table->decimal('latitude', 10, 7)->default(0.0); // خط العرض
            $table->decimal('longitude', 10, 7)->default(0.0); // خط الطول
            $table->integer('status')->default(0); // 0 افتراضي - 9 محذوف
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};

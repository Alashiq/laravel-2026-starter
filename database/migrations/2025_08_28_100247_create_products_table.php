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
        Schema::create('products', function (Blueprint $table) {
            $table->id();


            $table->string('name');                   // اسم المنتج
            $table->text('description')->nullable();  // وصف المنتج
            $table->string('photo')->nullable();      // صورة للمنتج

            $table->text('ingredients')->nullable();       // المكونات
            $table->text('keywords')->nullable();          // كلمات دلالية للبحث


            $table->foreignId('caterer_id')->nullable()
                ->constrained('caterers')
                ->cascadeOnUpdate()
                ->nullOnDelete();                    // لو منتج خاص مرتبط بتشاركية


            $table->integer('status')->default(0); // 0 not active - 1 active - 9 deleted

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::create('caterer_products', function (Blueprint $table) {
            $table->id();


            $table->foreignId('caterer_id')
                ->constrained('caterers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);          // سعر المنتج عند التشاركية

            $table->boolean('is_available')->default(true); // هل المنتج متوفر أو غير متوفر

            $table->unique(['caterer_id', 'product_id']); // كل تشاركية لا تضيف نفس المنتج مرتين


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caterer_products');
    }
};

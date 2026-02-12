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
        Schema::create('hall_booking_status_history', function (Blueprint $table) {
            $table->id();
            // الربط مع جدول حجوزات القاعات
            // cascadeOnDelete: إذا تم حذف الحجز، يتم حذف سجله التاريخي أيضاً
            $table->foreignId('booking_id')
                ->constrained('hall_bookings')
                ->cascadeOnDelete();

            $table->string('status'); // الحالة التي تم الانتقال إليها (e.g., 'confirmed', 'cancelled')
            $table->string('changed_by')->nullable()->comment('Can be: user, owner, system');
            $table->text('reason')->nullable(); // سبب التغيير (e.g., "Down payment expired")

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_booking_status_history');
    }
};

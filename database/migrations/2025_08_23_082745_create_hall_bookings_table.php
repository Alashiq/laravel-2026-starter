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
        Schema::create('hall_bookings', function (Blueprint $table) {
            $table->id();
            // الربط مع القاعة والمستخدم
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            // تفاصيل الموعد
            $table->date('booking_date');
            $table->enum('booking_period', ['morning', 'evening']);

            $table->enum('status', [
                'pending_approval',   // بانتظار موافقة المالك
                'pending_payment',    // بانتظار دفع العربون
                'confirmed',          // مؤكد (تم دفع العربون)
                'upcoming',           // اقترب الموعد
                'fully_paid',         // مدفوع بالكامل
                'in_progress',        // قيد التنفيذ (يوم المناسبة)
                'completed',          // مكتمل
                'rejected',           // مرفوض من المالك
                'cancelled',          // ملغي (من المستخدم أو النظام)
                'expired',            // انتهت صلاحية الدفع
            ])->default('pending_approval');


            // تفاصيل المناسبة (من إدخال المستخدم)
            $table->string('event_type');
            $table->enum('event_for', ['men', 'women', 'both']);
            $table->string('event_owner_name');

            // التفاصيل المالية
            $table->decimal('total_price', 12, 2);  // قيمة الحجز الكلي
            $table->decimal('down_payment_amount', 12, 2);  // قيمة العربون
            $table->timestamp('down_payment_paid_at')->nullable();  // وقت دفع العربون
            $table->decimal('remaining_amount', 12, 2);  // المبلغ المتبقي
            $table->timestamp('fully_paid_at')->nullable();  //وقت الدفع الكامل


            // توقيتات هامة
            $table->timestamp('expires_at')->nullable()->comment('For pending_payment status');


            $table->text('notes')->nullable();


            // 1. إضافة دعم الحذف الناعم
            $table->softDeletes(); // يضيف عمود `deleted_at`

            // 2. إنشاء فهرس فريد مشروط للحجوزات غير المحذوفة
            $table->unique(['hall_id', 'booking_date', 'booking_period', 'deleted_at'], 'active_booking_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_bookings');
    }
};

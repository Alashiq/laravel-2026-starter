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
        Schema::create('caterers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_id')->constrained('cities')->cascadeOnUpdate()->restrictOnDelete();

            // --- البيانات الأساسية ---
            $table->string('name');                                 // اسم التشاركية التجاري
            $table->string('logo')->nullable();                     // رابط صورة الشعار (Logo)
            $table->string('cover_photo')->nullable();              // رابط صورة الغلاف لصفحة التشاركية
            $table->text('description')->nullable();                // نبذة تعريفية عن التشاركية وما تقدمه

            // --- بيانات التواصل والموقع ---
            $table->string('address')->nullable();                  // العنوان التفصيلي لمقر التشاركية
            $table->string('phone')->nullable();                    // رقم الهاتف الأساسي للتواصل
            $table->string('whatsapp')->nullable();                 // رقم واتس آب المخصص للحجوزات والاستفسارات
            $table->decimal('latitude', 10, 7)->nullable();         // خط العرض لتحديد الموقع على الخريطة
            $table->decimal('longitude', 10, 7)->nullable();        // خط الطول لتحديد الموقع على الخريطة

            // --- سياسات الحجز والعمل ---
            $table->integer('min_booking_days_before')->default(30); // أقل عدد أيام للحجز قبل موعد المناسبة
            $table->decimal('min_order_value', 12, 2)->nullable();  // الحد الأدنى لقيمة الطلب للمناسبات (بالعملة المحلية)
            $table->decimal('deposit_percentage', 5, 2)->nullable(); // نسبة العربون المطلوبة للحجز (مثال: 25.50%)
            $table->text('cancellation_policy')->nullable();        // نص يشرح سياسة إلغاء الحجز واسترجاع العربون
            $table->integer('min_guests')->nullable();              // أقل عدد معازيم يمكن للتشاركية خدمتهم
            $table->integer('max_guests')->nullable();              // أقصى عدد معازيم يمكن للتشاركية خدمتهم

            // --- سياسات التذوق ---
            $table->boolean('offers_tasting_sessions')->default(false); // حقل عام: هل تقدم التشاركية جلسات تذوق (بشكل عام)؟
            $table->boolean('offers_tasting_booking')->default(false);  // حقل خاص بالتطبيق: هل تتيح الحجز لجلسة تذوق عبر التطبيق؟
            $table->text('tasting_policy')->nullable();                 // نص يشرح سياسة جلسات التذوق (التكلفة، المواعيد، ...)

            // --- معلومات إضافية ---
            $table->float('average_rating', 2, 1)->default(0.0);    // متوسط تقييمات العملاء (مثال: 4.5)
            $table->integer('reviews_count')->default(0);           // إجمالي عدد المراجعات والتقييمات التي تم استلامها
            $table->integer('status')->default(0);                  // حالة حساب التشاركية (0: غير نشط, 1: نشط, 9: محذوف)



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caterers');
    }
};

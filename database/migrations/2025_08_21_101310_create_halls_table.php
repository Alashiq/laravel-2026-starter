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
        Schema::create('halls', function (Blueprint $table) {
            $table->id();


            // الربط مع المدن
            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // البيانات الأساسية
            $table->string('name');                     // اسم القاعة
            $table->string('address')->nullable();      // العنوان التفصيلي
            $table->string('logo')->nullable();         // صورة رئيسية / شعار

            // بيانات التواصل
            $table->string('phone')->nullable();            // رقم الهاتف
            $table->string('whatsapp')->nullable();         // رقم واتس آب
            $table->string('supervisor_phone')->nullable(); // هاتف المشرف


            // تفاصيل القاعة
            $table->text('description')->nullable();    // نبذة عن القاعة
            $table->integer('tables')->nullable();      // عدد الطاولات
            $table->integer('chairs')->nullable();      // عدد المقاعد
            $table->integer('capacity')->nullable();    // السعة الكلية

            // أسعار الحجز
            $table->decimal('price_morning', 12, 2)->nullable();   // سعر الصباحي
            $table->decimal('price_evening', 12, 2)->nullable();   // سعر المسائي
            $table->decimal('price_full_day', 12, 2)->nullable();  // سعر اليوم الكامل


            // الخدمات
            $table->text('services_text')->nullable(); // وصف نصي للخدمات
            $table->boolean('drinks_service')->default(false); // خدمة توزيع المشروبات والمياه
            $table->boolean('buffet')->default(false);         // بوفيه
            $table->boolean('decoration')->default(false);     // ديكور/كوشة
            $table->boolean('sound_system')->default(false);   // نظام صوت/إضاءة
            $table->boolean('bride_room')->default(false);     // غرفة العروس
            $table->boolean('photography')->default(false);    // تصوير/فيديو
            $table->boolean('parking')->default(false);        // مواقف سيارات
            $table->boolean('air_conditioning')->default(true);// تكييف

            // الشروط والسياسات
            $table->decimal('deposit', 12, 2)->nullable();     // قيمة العربون
            $table->text('cancellation_policy')->nullable();   // بنود إلغاء الحجز
            $table->integer('final_payment_days')->nullable(); // الموعد النهائي لدفع كامل القيمة (بالأيام قبل الموعد)

            // الموقع على الخريطة
            $table->decimal('latitude', 10, 7)->nullable();    // خط العرض
            $table->decimal('longitude', 10, 7)->nullable();   // خط الطول


            $table->integer('status')->default(0); // 0 not active - 1 active - 9 deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }
};

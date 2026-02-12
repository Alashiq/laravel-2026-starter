<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'photo',
        'ingredients',
        'keywords',
        'caterer_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // لا يوجد حقول تحتاج إلى تحويل نوع بيانات خاص في هذا النموذج حاليًا
        // ولكن القسم جاهز إذا احتجت إليه مستقبلاً
    ];

    /**
     * Get the caterer that this product belongs to.
     * علاقة "ينتمي إلى" مع متعهد التموين
     */
    public function caterer()
    {
        // نستخدم `optional()` لجعل العلاقة اختيارية، حيث أن `caterer_id` يمكن أن يكون null
        return $this->belongsTo(related: Caterer::class, foreignKey: 'caterer_id');
    }

    /**
     * Get the full URL for the product photo.
     * Accessor لتحويل مسار الصورة إلى رابط كامل
     */
    public function getPhotoAttribute($value)
    {
        if ($value) {
            // يفترض أن الصور مخزنة في مجلد 'products' داخل 'storage/app/public'
            return url(Storage::url($value));
        }
        // يمكنك إرجاع رابط لصورة افتراضية في حالة عدم وجود صورة
        return null; 
    }

    /**
     * Scope a query to only include non-deleted products.
     * نطاق محلي لجلب السجلات غير المحذوفة (status != 9)
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }
}

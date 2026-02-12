<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'photo',
        'ingredients',
        'keywords',
        'caterer_id'
    ];

    // ✅ علاقة مع التشاركيات
    public function caterers()
    {
        return $this->belongsToMany(Caterer::class, 'caterer_products')
                    ->withPivot(['price', 'is_available'])
                    ->withTimestamps();
    }

    // ✅ علاقة للمنتجات الخاصة (caterer_id not null)
    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'caterer_id');
    }

    // ✅ معالجة رابط الصورة
    public function getPhotoAttribute($value)
    {
        if ($value) {
            return url(Storage::url($value));
        }
        return null;
    }
}

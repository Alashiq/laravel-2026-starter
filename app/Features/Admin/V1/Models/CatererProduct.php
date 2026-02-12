<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatererProduct extends Model
{
    use HasFactory;
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'caterer_products'; // تحديد اسم الجدول صراحةً

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'caterer_id',
        'product_id',
        'price',
        'is_available',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price'        => 'decimal:2', // تحويل السعر إلى رقم عشري
        'is_available' => 'boolean',   // تحويل الحقل إلى قيمة منطقية (true/false)
    ];

    /**
     * Get the caterer associated with this entry.
     * علاقة "ينتمي إلى" مع متعهد التموين
     */
    public function caterer()
    {
        return $this->belongsTo(Caterer::class);
    }

    /**
     * Get the product associated with this entry.
     * علاقة "ينتمي إلى" مع المنتج
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

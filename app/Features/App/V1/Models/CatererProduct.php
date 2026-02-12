<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CatererProduct extends Pivot
{
    use HasFactory;

    protected $table = 'caterer_products';

    public $timestamps = true;

    protected $fillable = [
        'caterer_id',
        'product_id',
        'price',
        'is_available',
    ];

    // ✅ المنتج
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ✅ التشاركية
    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'caterer_id');
    }
}

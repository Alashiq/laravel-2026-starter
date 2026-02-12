<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Caterer extends Model
{
    use HasFactory;

    protected $table = 'caterers';

    public $timestamps = true;

    protected $fillable = [];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'min_order_value' => 'decimal:2',
        'deposit_percentage' => 'decimal:2',
        'average_rating' => 'float',
        'offers_tasting_sessions' => 'boolean',
        'offers_tasting_booking' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(related: City::class, foreignKey: 'city_id');
    }


public function products()
{
    return $this->belongsToMany(Product::class, 'caterer_products')
                ->using(CatererProduct::class)   // <-- استخدام الموديل المخصص
                ->withPivot(['id','price', 'is_available'])
                ->withTimestamps();
}


    public function getLogoAttribute($value)
    {
        if ($value) {
            return url(Storage::url($value));
        }
    }

    public function getCoverPhotoAttribute($value)
    {
        if ($value) {
            return url(Storage::url($value));
        }
        return null;
    }


    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }

    public function scopeIsActive($query)
    {
        return $query->where('status', 1);
    }
}

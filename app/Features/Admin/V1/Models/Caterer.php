<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Caterer extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'city_id',
        'name',
        'logo',
        'cover_photo',
        'description',
        'address',
        'phone',
        'whatsapp',
        'latitude',
        'longitude',
        'min_booking_days_before',
        'min_order_value',
        'deposit_percentage',
        'cancellation_policy',
        'min_guests',
        'max_guests',
        'offers_tasting_sessions',
        'offers_tasting_booking',
        'tasting_policy',
        'average_rating',
        'reviews_count',
        'status',
    ];

        protected $casts = [
        'offers_tasting_sessions' => 'boolean',
        'offers_tasting_booking' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'deposit_percentage' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'average_rating' => 'float',
    ];


    public function city()
    {
        return $this->belongsTo(related: City::class, foreignKey: 'city_id');
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
        return null; // أو يمكنك إرجاع رابط لصورة افتراضية
    }

    
    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }
}

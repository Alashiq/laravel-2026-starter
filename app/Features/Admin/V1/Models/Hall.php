<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Hall extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'city_id',
        'address',
        'phone',
        'whatsapp',
        'supervisor_phone',
        'tables',
        'chairs',
        'capacity',
        'price_morning',
        'price_evening',
        'price_full_day',
        'deposit',
        'cancellation_policy',
        'services_text',
        'description',
        'final_payment_days',
        'latitude',
        'longitude',
        'logo',

        'drinks_service',
        'buffet',
        'decoration',
        'sound_system',
        'bride_room',
        'photography',
        'parking',
        'air_conditioning',

        'status',

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
    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }
}

<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Hall extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [

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

        public function scopeIsActive($query)
    {
        return $query->where('status', 1);
    }

        public function bookings()
    {
        return $this->hasMany(HallBooking::class);
    }
}

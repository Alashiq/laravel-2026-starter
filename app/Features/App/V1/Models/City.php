<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }
}

<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'status',
    ];



    public function scopeNotDeleted($query)
    {
        return $query->where('status', '<>', 9);
    }
}

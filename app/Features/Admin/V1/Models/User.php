<?php

namespace App\Features\Admin\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory;
    use HasApiTokens;
    protected $fillable = [
        'phone',
        'first_name',
        'last_name',
        'photo',
        'login_attempts',
        'attempts_at',
        'ban_expires_at',

        'otp',
        'otp_attempts',
        'otp_attempts_at',


        'city_id',


        'point',
        'balance',
        'status',
        'last_notification',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'otp',
        'remember_token',
    ];

    protected $casts = [
        'point' => 'integer',
        'status' => 'integer',
        'balance' => 'decimal:2',

    ];
    public function city()
    {
        return $this->belongsTo(City::class);
    }


    public function getPhotoAttribute($value)
    {
        if ($value) {
            return url(Storage::url($value));
        }

    }



}
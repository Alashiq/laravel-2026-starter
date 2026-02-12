<?php

namespace App\Features\App\v1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class HallBooking extends Model
{
    use HasFactory, SoftDeletes; // 2. استخدام SoftDeletes

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hall_bookings';


    public $timestamps = true;

    protected $fillable = [
        'hall_id',
        'user_id',
        'booking_date',
        'booking_period',
        'status',
        'event_type',
        'event_for',
        'event_owner_name',
        'total_price',
        'down_payment_amount',
        'down_payment_paid_at',
        'remaining_amount',
        'fully_paid_at',
        'expires_at',
        'notes',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'down_payment_paid_at' => 'datetime',
        'fully_paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'total_price' => 'decimal:2',
        'down_payment_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];


    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

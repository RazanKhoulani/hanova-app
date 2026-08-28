<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'inventory_reserved_at' => 'datetime',
        'inventory_released_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'inventory_reserved_at',
        'inventory_released_at',
        'total_amount',
        'payment_method',
        'payment_status',
        'delivery_method',
        'pickup_location',
        'delivery_area_id',
        'delivery_fee',
        'delivery_user_id',
        'coupon_id',
        'applied_offer_id',
        'discount_amount',
        'shipping_address',
        'tracking_status',
        'shipping_receipt',
        'is_confirmed',
        'qadmous_governorate', 'qadmous_branch', 'recipient_name', 'recipient_phone', 'tracking_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryArea()
    {
        return $this->belongsTo(DeliveryArea::class);
    }

    public function deliveryUser()
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function appliedOffer()
    {
        return $this->belongsTo(Offer::class, 'applied_offer_id');
    }
}

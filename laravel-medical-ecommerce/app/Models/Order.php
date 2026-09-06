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
        'receipt_reviewed_at' => 'datetime',
        'subtotal_amount' => 'decimal:2',
        'subtotal_usd' => 'decimal:2',
        'discount_amount_usd' => 'decimal:2',
        'delivery_fee_usd' => 'decimal:2',
        'total_amount_usd' => 'decimal:2',
    ];

    protected $fillable = [
        'user_id',
        'idempotency_key',
        'status',
        'inventory_reserved_at',
        'inventory_released_at',
        'total_amount',
        'subtotal_amount',
        'subtotal_usd',
        'total_amount_usd',
        'payment_method',
        'payment_status',
        'delivery_method',
        'pickup_location',
        'delivery_area_id',
        'delivery_fee',
        'delivery_fee_usd',
        'delivery_user_id',
        'coupon_id',
        'applied_offer_id',
        'discount_amount',
        'discount_amount_usd',
        'shipping_address',
        'shipping_latitude', 'shipping_longitude',
        'tracking_status',
        'shipping_receipt',
        'payment_receipt_status',
        'receipt_disk',
        'receipt_reviewed_by',
        'receipt_reviewed_at',
        'receipt_rejection_reason',
        'is_confirmed',
        'qadmous_location_id', 'qadmous_governorate', 'qadmous_branch', 'recipient_name', 'recipient_phone', 'tracking_number',
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

    public function qadmousLocation()
    {
        return $this->belongsTo(QadmousLocation::class);
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

    public function receiptReviewer()
    {
        return $this->belongsTo(User::class, 'receipt_reviewed_by');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}

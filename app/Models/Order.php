<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'order_amount',
        'vatpr',
        'vatamt',
        'payment_token',
        'payment_type',
        'payement_gateway_status',
        'payment_status',
        'order_status',
        'total_tax_amount',
        'payment_method',
        'status',
        'store_id',
        'tax_percentage',
        'partially_paid_amount',
        'is_guest',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'order_amount' => 'double',
        'vatpr' => 'double',
        'vatamt' => 'double',
        'total_tax_amount' => 'decimal:2',
        'status' => 'integer',
        'store_id' => 'integer',
        'tax_percentage' => 'double',
        'partially_paid_amount' => 'double',
        'is_guest' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }
}
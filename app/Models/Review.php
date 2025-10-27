<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';

    protected $fillable = [
        'item_id', // Assuming this is product_id
        'user_id',
        'comment',
        'attachment',
        'rating',
        'order_id',
        'item_campaign_id',
        'status',
        'module_id',
        'store_id',
        'reply',
        'review_id',
        'replied_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'item_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
        'order_id' => 'integer',
        'item_campaign_id' => 'integer',
        'status' => 'integer',
        'module_id' => 'integer',
        'store_id' => 'integer',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
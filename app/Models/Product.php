<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'image',
        'category_id',
        'barcode',
        'price',
        'tax',
        'tax_type',
        'discount',
        'discount_type',
        'status',
        'store_id',
        'unit_id',
        'order_count',
        'avg_rating',
        'rating_count',
        'rating',
        'stock',
        'maximum_cart_quantity',
        'images',
        'slug',
    ];

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'status' => 'integer',
        'store_id' => 'integer',
        'unit_id' => 'integer',
        'order_count' => 'integer',
        'avg_rating' => 'double',
        'rating_count' => 'integer',
        'stock' => 'integer',
        'maximum_cart_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'product_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'item_id'); // Assuming item_id refers to product_id
    }
}
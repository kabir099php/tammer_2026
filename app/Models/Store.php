<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'stores';
    protected $primaryKey = 'id';

    protected $fillable = [
        'vendor_id',
        'name',
        'phone',
        'email',
        'logo',
        'latitude',
        'longitude',
        'address',
        'status',
        'slug',
    ];

    protected $casts = [
        'id' => 'integer',
        'vendor_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'store_id');
    }
}
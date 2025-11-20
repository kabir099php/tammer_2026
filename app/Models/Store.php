<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Store extends Model
{
    protected $table = 'stores';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
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
        'user_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getvendorlist ()
    {
        return  User::all();//$this->belongsTo(User::class, 'vendor_id');
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
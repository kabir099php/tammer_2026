<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'image',
        'position',
        'status',
        'priority',
        'slug',
        'featured',
    ];

    protected $casts = [
        'id' => 'integer',
        'position' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'featured' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
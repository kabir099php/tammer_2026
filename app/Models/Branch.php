<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'city',
        'address',
        'phone',
    ];

    /**
     * Get the store that owns the branch.
     */
    public function store(): BelongsTo
    {
        // Assumes foreign key is 'store_id' and references App\Models\Store
        return $this->belongsTo(Store::class);
    }
}
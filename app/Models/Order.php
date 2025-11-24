<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'id';

    protected $fillable = [
        'country',
        'currency_code',
        'currency_symbol',
        'exchange_rate',
    ];

    protected $casts = [
        'id' => 'integer',
        'exchange_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
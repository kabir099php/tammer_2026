<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosDevice extends Model
{
    protected $table = 'pos_devices';
    protected $primaryKey = 'id';

    protected $fillable = [
        'device_id',
        'terminal_id',
        'name',
        'code',
        'connection_status',
        'store_id',
        'vendor_id',
        'branch_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'store_id' => 'integer',
        'vendor_id' => 'integer',
        'branch_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
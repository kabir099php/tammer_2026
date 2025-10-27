<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'translationable_type',
        'translationable_id',
        'locale',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'translationable_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Polymorphic relation accessors (optional but useful)
    public function translationable()
    {
        return $this->morphTo();
    }
}
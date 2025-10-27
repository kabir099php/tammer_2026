<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'f_name',
        'l_name',
        'phone',
        'crn',
        'email',
        'currency_id',
        'password',
        'branch',
        'account_no',
        'image',
        'status',
        'vat',
        'is_not_vat',
        'detail_page_footer',
        'firebase_token',
        'auth_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'integer',
        'currency_id' => 'integer',
        'status' => 'integer',
        'is_not_vat' => 'integer',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function stores()
    {
        return $this->hasMany(Store::class, 'vendor_id');
    }
}

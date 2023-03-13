<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Company extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'mobile',
        'email',
        'logo',
        'vat_number',
        'mushok_number',
        'contact_mobile',
        'status',
        'payment_term',
        'expiry_date',
        'discount_type'
    ];
}

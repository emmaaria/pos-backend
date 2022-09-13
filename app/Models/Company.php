<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
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
        'expiry_date'
    ];
}

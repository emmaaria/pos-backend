<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Invoice extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';

    protected $fillable = ['customer_id', 'invoice_id', 'total', 'comment', 'date', 'discount', 'discountAmount', 'discountType', 'company_id', 'paid_amount','grand_total','discount_setting', 'payment_method', 'user_id'];
}

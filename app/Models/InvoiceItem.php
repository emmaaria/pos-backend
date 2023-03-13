<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class InvoiceItem extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    protected $fillable = ['invoice_id', 'product_id', 'price', 'quantity', 'total', 'date','company_id','discount_type','discount','discount_amount','grand_total', 'user_id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class PurchaseItem extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';
    protected $fillable = ['purchase_id', 'product_id', 'price', 'quantity', 'total', 'date','company_id', 'user_id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Product extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    protected $fillable = ['name', 'product_id', 'category', 'unit', 'price', 'purchase_price', 'weight','company_id'];
}

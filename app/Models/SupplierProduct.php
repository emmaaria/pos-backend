<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class SupplierProduct extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;

    protected $fillable = ['supplier_id', 'product_id', 'company_id'];
}

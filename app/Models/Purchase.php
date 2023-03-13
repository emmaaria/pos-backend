<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Purchase extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    protected $fillable = ['supplier_id', 'purchase_id', 'amount', 'paid', 'comment', 'date','company_id', 'opening', 'user_id', 'payment_method'];
}

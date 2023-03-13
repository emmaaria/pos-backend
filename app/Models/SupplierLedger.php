<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class SupplierLedger extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';
    protected $fillable = ['supplier_id', 'transaction_id', 'type', 'due', 'date', 'comment','deposit','reference_no','company_id', 'user_id'];
}

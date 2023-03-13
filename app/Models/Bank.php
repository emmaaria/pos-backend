<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Rennokki\QueryCache\Traits\QueryCacheable;

class Bank extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;

    protected $fillable = ['name', 'account_name', 'account_no', 'branch', 'company_id', 'bank_type'];
}

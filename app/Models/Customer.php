<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Customer extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    protected $fillable = ['name','mobile', 'address','company_id'];
}

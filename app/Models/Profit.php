<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Profit extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';
    protected $fillable = ['company_id', 'deposit', 'deduct', 'date', 'reference_no'];
}

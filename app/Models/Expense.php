<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Expense extends Model
{
    use HasFactory;
    use QueryCacheable;
    public $cacheFor = 3600;
    public $cacheDriver = 'file';
    protected $fillable = ['expense_id','category','note','account','amount','company_id', 'date', 'user_id'];
}

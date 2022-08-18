<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBook extends Model
{
    use HasFactory;
    protected $fillable = ['transaction_id','type','payment','receive','date','reference_no','comment','company_id'];
}

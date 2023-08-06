<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;
    protected $fillable = ['return_id','invoice_id','return_amount','note','date','company_id', 'account', 'type', 'user_id', 'customer_id'];
}

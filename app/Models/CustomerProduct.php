<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProduct extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'product_id', 'company_id', 'price'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AveragePurchasePrice extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'price', 'company_id'];
}

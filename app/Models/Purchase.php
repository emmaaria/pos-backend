<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $fillable = ['supplier_id', 'purchase_id', 'amount', 'paid', 'comment', 'date','company_id', 'opening', 'user_id'];
}

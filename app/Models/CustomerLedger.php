<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLedger extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'transaction_id', 'type', 'amount', 'date', 'comment'];
}

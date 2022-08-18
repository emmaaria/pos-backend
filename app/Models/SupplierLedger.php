<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    use HasFactory;
    protected $fillable = ['supplier_id', 'transaction_id', 'type', 'due', 'date', 'comment','deposit','reference_no','company_id'];
}

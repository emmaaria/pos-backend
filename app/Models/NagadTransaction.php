<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NagadTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['transaction_id','type','withdraw','deposit','date','reference_no','comment','company_id', 'user_id'];
}

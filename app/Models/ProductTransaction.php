<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'transaction_id',
        'transaction_type'
    ];
}

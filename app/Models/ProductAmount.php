<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAmount extends Model
{
    use SoftDeletes;
    
    protected $table = 'product_amount';
}

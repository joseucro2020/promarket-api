<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status', 'type', 'limit', 'name', 'percentage', 'start', 'end',
        'quantity_product', 'minimum_purchase', 'quantity_purchase'
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}

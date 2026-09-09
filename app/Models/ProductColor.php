<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    protected $table = 'product_colors';

    public function amounts()
    {
        return $this->hasMany(ProductAmount::class, 'product_color_id');
    }
}

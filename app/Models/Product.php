<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('main', '1');
    }

    public function amounts()
    {
        return $this->hasManyThrough(ProductAmount::class, ProductColor::class, 'product_id', 'product_color_id');
    }
}

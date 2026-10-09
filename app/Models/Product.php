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

    public function offers()
    {
        return $this->belongsToMany(Offer::class);
    }

    public function offersActive()
    {
        return $this->offers()->where('status', Offer::ACTIVE);
    }

    public function getOffersActiveAttribute()
    {
        return $this->offersActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->get();
    }

    public function getOfferAttribute()
    {
        if ($this->offers->count() > 0) {
            return $this->offersActive()->whereDate('start', '<=', date('Y-m-d'))->first();
        }
        return null;
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }

    public function discountsActive()
    {
        return $this->discounts()->where('status', Discount::ACTIVE);
    }

    public function getDiscountsActiveAttribute()
    {
        return $this->discountsActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->get();
    }

    public function getDiscountAttribute()
    {
        if ($this->discounts->count() > 0) {
            return $this->discountsActive()->whereDate('start', '<=', date('Y-m-d'))->first();
        }
        return null;
    }
}

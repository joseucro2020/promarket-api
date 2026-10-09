<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = ['percentage', 'start', 'end'];

    const ACTIVE = 1;
    const INACTIVE = 0;

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}

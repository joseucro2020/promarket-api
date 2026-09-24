<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'address',
        'address_line2',
        'city',
        'state',
        'zip',
        'lat',
        'long',
        'name',
        'country_code',
        'phone',
        'email',
        'is_default',
        'tag',
        'reference'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

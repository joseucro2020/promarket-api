<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'lat',
        'lng',
        'name',
        'country_code',
        'phone',
        'email',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

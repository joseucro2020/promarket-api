<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = "estados";
    public $timestamps = false;
    const STATUS_ACTIVE = 1;

    public function municipalities()
    {
        return $this->hasMany(Municipality::class, 'estado_id');
    }
}

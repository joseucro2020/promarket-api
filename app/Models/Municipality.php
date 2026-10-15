<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    const STATUS_ACTIVE = 1;

    protected $table = 'municipalities';
    protected $fillable = ['name', 'estado_id', 'status', 'free'];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}

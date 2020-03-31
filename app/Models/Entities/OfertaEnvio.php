<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class OfertaEnvio extends Model
{
    protected $table = 'ofertaenvio';
    protected $primaryKey = 'idofertaenvio';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

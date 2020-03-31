<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'ofertaenvio';
    protected $primaryKey = 'idofertaenvio';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table = 'conductor';
    protected $primaryKey = 'idconductor';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $table = 'envio';
    protected $primaryKey = 'idenvio';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';
    protected $primaryKey = 'id_address';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

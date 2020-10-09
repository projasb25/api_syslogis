<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organization';
    protected $primaryKey = 'id_organization';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

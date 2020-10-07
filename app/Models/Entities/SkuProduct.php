<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class SkuProduct extends Model
{
    protected $table = 'sku_product';
    protected $primaryKey = 'id_sku_product';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];
}

<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $table = 'guide';
    protected $primaryKey = 'id_guide';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];

    public function sku_product()
    {
        return $this->hasMany('App\Models\Entities\SkuProduct', 'id_guide', 'id_guide');
    }

    public function address()
    {
        return $this->hasOne('App\Models\Entities\Address', 'id_address', 'id_address');
    }

    public function organization()
    {
        return $this->hasOne('App\Models\Entities\Organization', 'id_organization', 'id_organization');
    }
}

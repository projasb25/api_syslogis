<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $table = 'pedido_detalle';
    protected $primaryKey = 'idpedido_detalle';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = ['punto_latitud_descarga,punto_longitud_descarga'];
}

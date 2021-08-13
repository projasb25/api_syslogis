<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\actualizarPedido;
use App\Http\Requests\Pedido\grabarImagen;
use App\Http\Requests\Pedido\obtenerImagen;
use App\Models\Services\PedidoService;
use Illuminate\Http\Request;
use Validator;

class PedidoController extends Controller
{
    protected $pedidoServi;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoServi = $pedidoService;
    }

    public function grabarImagen(Request $request)
    {
        $rules = [
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:60048',
        ];

        $messages = [
            'imagen.required' => 'La imagen es requerida.',
            'imagen.mimes'  => 'Extension invalida.',
            'imagen.max' => 'Tamaño de Imagen invalido'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            dd($validator->getMessageBag());
            $errors = $validator->getMessageBag()->messages();
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $errors[array_key_first($errors)][0],
                ]
            ], 400);
        }

        return $this->pedidoServi->grabarImagen($request);
    }

    public function grabarImagenPedido(grabarImagen $request)
    {
        return $this->pedidoServi->grabarImagenPedido($request);
    }
    

    // public function getImagen(Request $request, $idpedido_detalle)
    // {
    //     return $this->pedidoServi->getImagen($idpedido_detalle);
    // }

    // public function actualizar(Request $request)
    // {
    //     $rules = [
    //         'idpedido_detalle' => 'required|numeric',
    //         'estado' => 'required|string',
    //         'observacion' => 'string|nullable',
    //         'latitud' => 'string|numeric',
    //         'longitud' => 'string|numeric'
    //     ];

    //     $messages = [
    //         'idpedido_detalle.required' => 'Falta idpedido_detalle',
    //         'idpedido_detalle.numeric' => 'pedido invalido',
    //         'estado.*' => 'estado inválido.',
    //         'observacion.*' => 'Observación inválida.',
    //         'latitud.*' => 'Latitud Inválida',
    //         'longitud.*' => 'Longitud Inválida.'
    //     ];

    //     $validator = Validator::make($request->all(), $rules, $messages);

    //     if ($validator->fails()) {
    //         $errors = $validator->getMessageBag()->messages();
    //         return response()->json([
    //             'success' => false,
    //             'error' => [
    //                 'message' => $errors[array_key_first($errors)][0],
    //             ]
    //         ], 400);
    //     }

    //     return $this->pedidoServi->actualizarPedido($request);
    // }

    // public function getMotivos(Request $request, $idcliente)
    // {
    //     return $this->pedidoServi->getMotivos($idcliente);
    // }

    // public function getAgencias(Request $request, $idcliente)
    // {
    //     return $this->pedidoServi->getAgencias($idcliente);
    // }
}

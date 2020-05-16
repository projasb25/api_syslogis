<?php

namespace App\Http\Controllers;

use App\Models\Repositories\ConductorRepository;
use App\Models\Services\OfertasService;
use Illuminate\Http\Request;
use Validator;

class ConductorController extends Controller
{
    private $ofertasServi;
    private $conductorRepo;

    public function __construct(OfertasService $ofertasService, ConductorRepository $conductorRepository)
    {
        $this->ofertasServi = $ofertasService;
        $this->conductorRepo = $conductorRepository;
    }

    public function listarOfertas(Request $request)
    {
        $res = $this->ofertasServi->listar($request);
        return response()->json($res);
    }

    public function actualizarEstado(Request $request)
    {
        $rules = [
            'estado' => 'required|boolean',
        ];

        $messages = [
            'estado.required' => 'Estado requerido.',
            'estado.boolean'  => 'Estado inválida.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->messages();
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $errors[array_key_first($errors)][0],
                ]
            ], 400);
        }
        
        $conductor = auth()->user()->idconductor;
        if ($request->get('estado')) {
            $estado = 'DISPONIBLE';
        } else {
            $estado = 'NO DISPONIBLE';
        }

        $this->conductorRepo->ActualizarEstado($conductor, $estado);

        return response()->json([
            'success' => true,
            'data' => [
                'mensaje' => 'Estado actualizado'
            ]
        ]);
    }
}

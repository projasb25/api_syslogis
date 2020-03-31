<?php

namespace App\Http\Controllers;

use App\Http\Requests\Conductor\actualizarEstado;
use App\Models\Repositories\ConductorRepository;
use App\Models\Services\OfertasService;
use Illuminate\Http\Request;

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

    public function actualizarEstado(actualizarEstado $request)
    {
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

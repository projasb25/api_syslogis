<?php

namespace App\Models\Services;

use App\Models\Entities\OfertaEnvio;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfertasService
{
    protected $ofertasEnvioRep;
    protected $conductorRepo;

    public function __construct(OfertasEnvioRepository $ofertasEnvioRepository, ConductorRepository $conductorRepository)
    {
        $this->ofertasEnvioRep = $ofertasEnvioRepository;
        $this->conductorRepo = $conductorRepository;
    }

    public function listar(Request $request)
    {
        $res['success'] = false;
        
        try {
            $user = auth()->user();
            $ofertas = $this->conductorRepo->get_ofertas($user->idconductor);

            $res['data'] = $ofertas;
            $res['success'] = true;
        } catch (Exception $e) {
            Log::warning('Listar Ofertas:', ['exception' => $e->getMessage(), 'user' => $user ]);
            throw $e;
        }
        return $res;
    }
}

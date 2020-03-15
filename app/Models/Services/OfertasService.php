<?php

namespace App\Models\Services;

use App\Models\Entities\OfertaEnvio;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use Carbon\Carbon;
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
            $aceptadas = $this->conductorRepo->getOfertasActivas($user->idconductor);

            foreach ($ofertas as $key => $oferta) {
                if (!$aceptadas) {
                    $disabled = false;
                } else {
                    if (Carbon::parse($oferta->fecha_creacion)->diffInDays($aceptadas->fecha_creacion) <> 0) {
                        $disabled = true;
                    } else {
                        $disabled = false;
                    }
                }
                $oferta->disabled = $disabled;
            }
            dd($ofertas);










            // $fec = Carbon::parse($aceptadas->fecha_creacion);
            if ($aceptadas) {
                foreach ($ofertas as $key => $value) {
                    echo Carbon::parse($value->fecha_creacion)->diffInDays($aceptadas->fecha_creacion) . '<br>';

                    // if (Carbon::parse($value->fecha_creacion)->gt($aceptadas->fecha_creacion)) {
                    //     // edited at is newer than created at
                    //     echo $value->fecha_creacion . '<br>';
                    //     echo $aceptadas->fecha_creacion . '<br>';
                    //     die();
                    //     dd('test');
                    // }
                }
            }

            die();
            dd($fecha_creacion);
            $searchedValue = 'ACEPTADO';
            $aceptadas = array_filter(
                $ofertas->toArray(),
                function ($e) use (&$searchedValue) {
                    return $e->ofertaconductor_estado == $searchedValue;
                }
            );

            if (empty($aceptadas)) {
                // $fecha = $aceptadas[0]->fecha_creacion
            }


            $res['data'] = $ofertas;
            $res['success'] = true;
        } catch (Exception $e) {
            Log::warning('Listar Ofertas:', ['exception' => $e->getMessage(), 'user' => $user]);
            throw $e;
        }
        return $res;
    }
}

<?php

namespace App\Models\Services;

use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use App\Models\Repositories\PedidoDetalleRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnviosService
{
    protected $pedidoDetalleRepo;
    protected $ofertasEnvioRepo;
    protected $conductorRepo;

    public function __construct(
        PedidoDetalleRepository $pedidoDetalleRepository,
        OfertasEnvioRepository $ofertasEnvioRepository,
        ConductorRepository $conductorRepository
    ) {
        $this->pedidoDetalleRepo = $pedidoDetalleRepository;
        $this->ofertasEnvioRepo = $ofertasEnvioRepository;
        $this->conductorRepo = $conductorRepository;
    }

    public function aceptar(Request $request)
    {
        $res['success'] = false;
        try {
            # Obtenemos los datos de la ofertaenvio
            $oferta_envio = $this->ofertasEnvioRepo->getOferta($request->idofertaenvio);
            if (!$oferta_envio) {
                return Res::error(['Oferta no encontrada.', 2001], 404);
            } elseif ($oferta_envio->estado_ofertaenvio !== 'ACTIVO') {
                return Res::error(['Lo sentimos, la oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }

            # Obtener los envios pertenecientes a la oferta para actualizar las coordenadas
            $detalle_envio = $this->pedidoDetalleRepo->getPedidos($request->idofertaenvio);
            $this->obtenerCoordenadas($request->idofertaenvio, $detalle_envio);

            $datosVehiculo = $this->conductorRepo->getDatosVehiculo(auth()->user()->idconductor);
            $this->ofertasEnvioRepo->acpetarOferta($request->idofertaenvio, $datosVehiculo, $detalle_envio);
        } catch (Exception $e) {
            Log::warning('Aceptar envio ', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            throw $e;
        }

        return Res::success('Oferta aceptada correctamente.');
    }

    public function sanitizeAdress($adress)
    {
        $dont = ['$', '#', '&', '"', '/', '(', ')', '-'];
        return str_replace($dont, '', $adress);
    }

    public function listarRutas($idofertaenvio)
    {
        $res['success'] = false;
        $rutas = $this->pedidoDetalleRepo->getPedidosApp($idofertaenvio);
        $res['data'] = $rutas;
        $res['success'] = true;

        return $res;
    }

    public function obtenerCoordenadas($idofertaenvio, $pedidos)
    {
        $coordenadas = [];
        foreach ($pedidos as $key => $value) {
            # Limpiamos la direccion para que no haya problemas con la api de google
            $direccion = $this->sanitizeAdress($value->direccion_descarga);

            try {
                $client = new Client(['base_uri' => env('GOOGLEAPIS_GEOCODE_URL')]);
                $url = "json?address=Peru," . $direccion . "&key=" . env('GOOGLEAPIS_GEOCODE_KEY');

                $req = $client->request('GET', $url);
                $resp = json_decode($req->getBody()->getContents());

                $lat = (empty($resp->results)) ? null : $resp->results[0]->geometry->location->lat;
                $lng = (empty($resp->results)) ? null : $resp->results[0]->geometry->location->lng;
            } catch (RequestException $e) {
                Log::warning('Obtener coordenadas: hubo un problema con la api de google.', [
                    'endpoint' => $url,
                    'idpedido_detalle' => $value->idpedido_detalle,
                    'direccion' => $direccion
                ]);
                $lat = null;
                $lng = null;
            }

            array_push($coordenadas, [
                'idpedido_detalle' => $value->idpedido_detalle,
                'punto_latitud_descarga' => $lat,
                'punto_longitud_descarga' => $lng
            ]);
        }

        try {
            $this->pedidoDetalleRepo->actualizarCoordenadas($coordenadas);
            Log::info('Coordenadas actualizadas con exito', [
                'idofertaenvio' => $idofertaenvio,
                'nro_registros_actualizados' => $pedidos->count()
            ]);
        } catch (Exception $e) {
            Log::warning('Obtener coordenadas ', ['exception' => $e->getMessage()]);
            Res::error(['No se pudo actualizar las coordenadas de los envios.', 2003], 500);
        }
    }
}

<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\EnvioRepository;
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
    protected $envioRepo;

    public function __construct(
        PedidoDetalleRepository $pedidoDetalleRepository,
        OfertasEnvioRepository $ofertasEnvioRepository,
        ConductorRepository $conductorRepository,
        EnvioRepository $envioRepository
    ) {
        $this->pedidoDetalleRepo = $pedidoDetalleRepository;
        $this->ofertasEnvioRepo = $ofertasEnvioRepository;
        $this->conductorRepo = $conductorRepository;
        $this->envioRepo = $envioRepository;
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

        return Res::success(['mensaje' => 'Oferta aceptada correctamente.']);
    }

    public function sanitizeAdress($adress)
    {
        $dont = ['$', '#', '&', '"', '/', '(', ')', '-'];
        return str_replace($dont, '', $adress);
    }

    public function listarRutas($idofertaenvio)
    {
        try {
            $rutas = $this->pedidoDetalleRepo->getPedidosApp($idofertaenvio);
            if (!$rutas->count()) {
                throw new CustomException(['No existen rutas ascociadas a este id.', 2007], 404);
            }
        } catch (CustomException $e) {
            Log::warning('Listar rutas error', ['exception' => $e->message(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Listar rutas error', ['exception' => $e->getMessage(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error($e->getMessage(), $e->getCode());
        }
        return Res::success($rutas);
    }

    public function obtenerCoordenadas($idofertaenvio, $pedidos)
    {
        $coordenadas = [];
        foreach ($pedidos as $key => $value) {
            if (!$value->punto_latitud_descarga) {
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

    public function rechazar(Request $request)
    {
        $res['success'] = false;
        try {
            # Obtenemos los datos de la ofertaenvio
            $oferta_envio = $this->ofertasEnvioRepo->getOferta($request->idofertaenvio);
            if (!$oferta_envio) {
                return Res::error(['Oferta no encontrada.', 2001], 404);
            } elseif ($oferta_envio->estado !== 'ESPERA') {
                return Res::error(['Lo sentimos, la oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }

            $this->ofertasEnvioRepo->rechazarOferta($request->idofertaenvio);
            Log::info('Oferta Rechazada con exito', [
                'idofertaenvio' => $request->idofertaenvio,
            ]);
        } catch (Exception $e) {
            Log::warning('Aceptar envio ', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            throw $e;
        }

        return Res::success(['mensaje' => 'Oferta rechazada correctamente.']);
    }

    public function iniciar(Request $request, $idenvio)
    {
        try {
            $envio = $this->envioRepo->get($idenvio);
            if (!$envio) {
                return Res::error(['Envio no encontrado.', 2004], 404);
            } elseif (!in_array($envio->estado, ['ACEPTADO', 'ASIGNADO'])) {
                return Res::error(['Envio ya esta iniciado o fue cancelado.', 2005], 400);
            }
            $this->envioRepo->iniciar($idenvio);
        } catch (Exception $e) {
            Log::warning('Iniciar envio ', ['exception' => $e->getMessage(), 'idenvio' => $idenvio]);
            throw $e;
        }
        return Res::success(['mensaje' => 'Envio iniciado correctamente.']);
    }
}

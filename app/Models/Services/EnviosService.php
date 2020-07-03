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
                throw new CustomException(['Oferta no encontrada.', 2001], 404);
            } elseif ($oferta_envio->estado_ofertaenvio !== 'ACTIVO') {
                throw new CustomException(['La oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }

            # Obtener los envios pertenecientes a la oferta para actualizar las coordenadas
            $detalle_envio = $this->pedidoDetalleRepo->getPedidos($request->idofertaenvio);
            $coordenadas = $this->obtenerCoordenadas($request->idofertaenvio, $detalle_envio);
            
            if (!$coordenadas['success']) {
                throw new CustomException([$coordenadas['mensaje'], 2001], 404);
            }

            $datosVehiculo = $this->conductorRepo->getDatosVehiculo(auth()->user()->idconductor);
            $this->ofertasEnvioRepo->acpetarOferta($request->idofertaenvio, $datosVehiculo, $detalle_envio);

            Log::info('Oferta Aceptada con exito', [
                'idofertaenvio' => $request->idofertaenvio,
                'nro_paradas' => $detalle_envio->count()
            ]);
        } catch (CustomException $e) {
            Log::warning('Aceptar oferta Error', ['exception' => $e->message(), 'idofertaenvio' => $request->idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Aceptar oferta Error', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            return Res::error(['Error al Aceptar la oferta', 2004], 400);
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
            // if ($idofertaenvio === '24049') {
            //     $rutas = $this->pedidoDetalleRepo->getPedidosApp2($idofertaenvio);
            // } else {
            //     $rutas = $this->pedidoDetalleRepo->getPedidosApp($idofertaenvio);
            // }
            
            $rutas = $this->pedidoDetalleRepo->sp_listar_pedidos($idofertaenvio);

            if (!count($rutas)) {
                throw new CustomException(['No existen rutas ascociadas a este id.', 2007], 404);
            }
        } catch (CustomException $e) {
            Log::warning('Listar rutas error', ['exception' => $e->message(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Listar rutas error', ['exception' => $e->getMessage(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error(['Error al Listar las rutas', 2004], 400);
        }
        return Res::success($rutas);
    }

    public function obtenerCoordenadas($idofertaenvio, $pedidos)
    {
        $res['success'] = false;
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

                    if (empty($resp->results)) {
                        Log::warning('Obtener coordenadas nula de google.', ['direccion' => $direccion , 'response' => array($resp), 'url' => $url]);
                        $lat = null;
                        $lng = null;
                    } else {
                        $lat = $resp->results[0]->geometry->location->lat;
                        $lng = $resp->results[0]->geometry->location->lng;
                    }
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

            $res['success'] = true;
            Log::info('Obtener coordenadas con exito', [
                'idofertaenvio' => $idofertaenvio,
                'nro_registros_actualizados' => count($coordenadas)
            ]);
        } catch (Exception $e) {
            Log::warning('Obtener coordenadas error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = 'Error al actualizar las coordenadas de los envios.'; 
        }

        return $res;
    }

    public function rechazar(Request $request)
    {
        $res['success'] = false;
        try {
            # Obtenemos los datos de la ofertaenvio
            $oferta_envio = $this->ofertasEnvioRepo->getOferta($request->idofertaenvio);
            if (!$oferta_envio) {
                throw new CustomException(['Oferta no encontrada.', 2001], 404);
            } elseif ($oferta_envio->estado !== 'ESPERA') {
                throw new CustomException(['La oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }

            $this->ofertasEnvioRepo->rechazarOferta($request->idofertaenvio);
            Log::info('Oferta Rechazada con exito', [
                'idofertaenvio' => $request->idofertaenvio,
            ]);
        } catch (CustomException $e) {
            Log::warning('Rechazar oferta Error', ['exception' => $e->message(), 'idofertaenvio' => $request->idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Rechazar oferta Error', ['exception' => $e->getMessage(), 'idofertaenvio' => $request->idofertaenvio]);
            return Res::error(['Error al rechazar la oferta', 2004], 400);
        }
        return Res::success(['mensaje' => 'Oferta rechazada correctamente.']);
    }

    public function iniciar(Request $request, $idenvio)
    {
        try {
            $envio = $this->envioRepo->get($idenvio);
            if (!$envio) {
                throw new CustomException(['Envio no encontrado.', 2004], 404);
            } elseif (!in_array($envio->estado, ['ACEPTADO', 'ASIGNADO'])) {
                throw new CustomException(['Envio ya esta iniciado o fue cancelado.', 2005], 400);
            }
            $this->envioRepo->iniciar($idenvio);

            Log::info('Envio iniciado con exito', ['idenvio' => $idenvio, 'idconductor' => auth()->user()->idconductor]);
        } catch (CustomException $e) {
            Log::warning('Iniciar oferta Error', ['exception' => $e->message(), 'idenvio' => $idenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Iniciar oferta Error ', ['exception' => $e->getMessage(), 'idenvio' => $idenvio]);
            return Res::error(['Error al Inicar la oferta', 2004], 400);
        }
        return Res::success(['mensaje' => 'Envio iniciado correctamente.']);
    }

    public function finalizar($idenvio)
    {
        try {
            $envio = $this->envioRepo->get($idenvio);
            if (!$envio) {
                throw new CustomException(['Envio no encontrado', 2020], 400);
            } elseif ($envio->estado !== 'CURSO') {
                throw new CustomException(['El envio tiene que haber sido aceptado e iniciado para poder finalizar', 2021], 400);
            }

            $this->envioRepo->finalizar($idenvio);
            Log::info('Envio finalizado con exito', ['idenvio' => $idenvio, 'idconductor' => auth()->user()->idconductor]);
        } catch (CustomException $e) {
            Log::warning('Finalizar Envio error', ['exception' => $e->getData()[0], 'idenvio' => $idenvio, 'idconductor' => auth()->user()->idconductor]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Finalizar Envio error', ['exception' => $e->getMessage(), 'idenvio' => $idenvio, 'idconductor' => auth()->user()->idconductor]);
            return Res::error(['Error al finalizar el envio', 2004], 400);
        }
        return Res::success(['mensaje' => 'Envio finalizado correctamente.']);
    }

    public function coordenadas($idofertaenvio)
    {
        $res['success'] = false;
        try {
            # Obtenemos los datos de la ofertaenvio
            $oferta_envio = $this->ofertasEnvioRepo->getOferta($idofertaenvio);
            if (!$oferta_envio) {
                throw new CustomException(['Oferta no encontrada.', 2001], 404);
            }

            # Obtener los envios pertenecientes a la oferta para actualizar las coordenadas
            $detalle_envio = $this->pedidoDetalleRepo->getPedidosActivos($idofertaenvio);
            if (!$detalle_envio->count()) {
                throw new CustomException(['La Oferta fue cancelada o finalizada.', 2008], 400);
            }
            
            $coordenadas = $this->obtenerCoordenadas($idofertaenvio, $detalle_envio);
            if (!$coordenadas['success']) {
                throw new CustomException([$coordenadas['mensaje'], 2001], 404);
            }

            Log::info('Coordenadas Actualizadas con exito', [
                'idofertaenvio' => $idofertaenvio
            ]);
        } catch (CustomException $e) {
            Log::warning('Actualizar Coordenadas error', ['exception' => $e->message(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Actualizar Coordenadas error', ['exception' => $e->getMessage(), 'idofertaenvio' => $idofertaenvio]);
            return Res::error(['Error al actualizar las coordenadas', 2004], 400);
        }
        return Res::success(['mensaje' => 'Coordenadas actualizadas correctamente.']);
    }
}
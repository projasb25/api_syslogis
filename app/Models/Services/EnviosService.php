<?php

namespace App\Models\Services;

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

    public function __construct(PedidoDetalleRepository $pedidoDetalleRepository)
    {
        $this->pedidoDetalleRepo = $pedidoDetalleRepository;
    }

    public function aceptar(Request $request)
    {
        $res['success'] = false;
        $update = [];
        try {
            # Obtener los envios pertenecientes a la oferta
            $detalle_envio = $this->pedidoDetalleRepo->getPedidos($request->get('idofertaenvio'));

            // validaciones;

            # Buscamos coordenadas
            foreach ($detalle_envio as $key => $value) {
                # Limpiamos la direccion para que no haya problemas con la api de google
                $direccion = $this->sanitizeAdress($value->direccion_descarga);

                $client = new Client(['base_uri' => env('GOOGLEAPIS_GEOCODE_URL')]);
                $req = $client->request('GET', "json?address=Peru," . $direccion . "&key=" . env('GOOGLEAPIS_GEOCODE_KEY'));
                $resp = json_decode($req->getBody()->getContents());

                $lat = (empty($resp->results)) ? null : $resp->results[0]->geometry->location->lat;
                $lng = (empty($resp->results)) ? null : $resp->results[0]->geometry->location->lng;

                array_push($update, [
                    'idpedido_detalle' => $value->idpedido_detalle,
                    'punto_latitud_descarga' => $lat,
                    'punto_longitud_descarga' => $lng
                ]);
            }
            $this->pedidoDetalleRepo->actualizarCoordenadas($update);
            $res['data'] = ['mensaje' => 'Oferta aceptada'];
            $res['success'] = true;
        } catch (RequestException $e) {
            Log::info('Request api Google ', ['exception' => $e->getMessage()]);
            throw $e;
        } catch (Exception $e) {
            Log::warning('Aceptar envio ', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            throw $e;
        }
        return $res;
    }

    public function sanitizeAdress($adress)
    {
        $dont = ['$', '#', '&', '"', '/', '(', ')'];
        return str_replace($dont, '', $adress);
    }
}

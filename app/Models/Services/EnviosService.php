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
            // obtener los envios
            $detalle_envio = $this->pedidoDetalleRepo->getPedidos($request->get('idofertaenvio'));
            // dd($detalle_envio);
            // validaciones;

            # Buscamos coordenadas
            foreach ($detalle_envio as $key => $value) {
                $client = new Client(['base_uri' => env('GOOGLEAPIS_GEOCODE_URL')]);
                $req = $client->request('GET', "json?address=" . $value->direccion_descarga . ",PERU&key=" . env('GOOGLEAPIS_GEOCODE_KEY'));
                $resp = json_decode($req->getBody()->getContents());

                Log::info('respuesta correcta ', ['res' => $resp->results[0]->geometry]);
                if (empty($resp->results)) {
                    $lat = null;
                    $lng = null;
                } else {
                    $lat = $resp->results[0]->geometry->location->lat;
                    $lng = $resp->results[0]->geometry->location->lng;
                }
                array_push($update, [
                    'idpedido_detalle' => $value->idpedido_detalle,
                    'punto_latitud_descarga' => $lat,
                    'punto_longitud_descarga' => $lng
                ]);
            }

            $this->pedidoDetalleRepo->actualizarCoordenadas($update);
        } catch (RequestException $e) {
            Log::info('Request api Google ', ['exception' => $e->getMessage()]);
            throw $e;
        } catch (Exception $e) {
            Log::warning('Aceptar envio ', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            throw $e;
        }
        return $res;
    }
}

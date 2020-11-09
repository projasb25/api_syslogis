<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\EnvioRepository;
use App\Models\Repositories\IntegracionRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use App\Models\Repositories\PedidoDetalleRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegracionService
{
    protected $repository;

    public function __construct(IntegracionRepository $integracionRepository) {
        $this->repository = $integracionRepository;
    }

    public function integracionRipley()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getIntegracionRipley();
            foreach ($guides as $key => $guide) {
                if ($guide->estado === 'CURSO') {
                    $guide->estado = 'En Transito';
                    $guide->subestado = 'En Ruta hacia el Cliente';
                }
                if ($guide->estado === 'ENTREGADO') {
                    $guide->estado = 'ENTREGADO';
                    $guide->subestado = 'ENTREGA EXITOSA';
                }

                if($guide->subestado ==="Ausente"){
                    $guide->subestado = "Sin Morador";
                }
                if($guide->subestado ==="Rechazado"){
                    $guide->subestado = "Suspende Entrega";
                }
                if($guide->subestado ==="Zona Peligrosa"){
                    $guide->subestado = utf8_decode("Acceso Difícil y/o Inaccesible");
                }
                if($guide->subestado ==="Siniestro"){
                    $guide->subestado = "Robo";
                }
                if($guide->subestado ==="Robo"){
                    $guide->subestado = "Robo";
                }
                if($guide->subestado ==="Producto Siniestrado"){
                    $guide->subestado = "Robo";
                }
                if($guide->subestado ==="Direccion errada"){
                    $guide->subestado = "Consignaron Datos Incorrectos";
                }
                if($guide->subestado ==="Direccion no existe"){
                    $guide->subestado = "Consignaron Datos Incorrectos";
                }
                if($guide->subestado ==="Direccion Insuficiente"){
                    $guide->subestado = "Zona restringida y/o sin referencia";
                }
                if($guide->subestado ==="Desconocido"){
                    $guide->subestado = "Documentacion Incorrecta";
                }
                if($guide->subestado ==="Acceso Difícil y/o Inaccesible"){
                    $guide->subestado = "Acceso Difícil y/o Inaccesible";
                }
                if($guide->subestado ==="Cambio Fecha de Despacho"){
                    $guide->subestado = "Cambio Fecha de Despacho";
                }
                if($guide->subestado ==="Anula Compra"){
                    $guide->subestado = "Anula Compra";
                }
                if($guide->subestado ==="Cambio de Producto"){
                    $guide->subestado = "Cambio de Producto";
                }
                if($guide->subestado ==="Cambio Fecha de Despacho"){
                    $guide->subestado = "Cambio Fecha de Despacho";
                }
                if($guide->subestado ==="Cliente no coordino recepción"){
                    $guide->subestado = "Cliente no coordino recepción";
                }
                if($guide->subestado ==="Consignaron Datos Incorrectos"){
                    $guide->subestado = "Consignaron Datos Incorrectos";
                    }
                if($guide->subestado ==="Documentacion Incorrecta"){
                    $guide->subestado = "Documentacion Incorrecta";
                }
                if($guide->subestado ==="Falto Personal de Apoyo"){
                    $guide->subestado = "Falto Personal de Apoyo";
                }
                if($guide->subestado ==="Fuera de Hora"){
                    $guide->subestado = "Fuera de Hora";
                }
                if($guide->subestado ==="Mercaderia Dañada"){
                    $guide->subestado = "Mercaderia Dañada";
                }
                if($guide->subestado ==="Mercadería Incompleta"){
                    $guide->subestado = "Mercadería Incompleta";
                }
                if($guide->subestado ==="Mercaderia Incorrecta"){
                    $guide->subestado = "Mercaderia Incorrecta";
                }
                if($guide->subestado ==="Sin Morador"){
                    $guide->subestado = "Sin Morador";
                }
                if($guide->subestado ==="Suspende Entrega"){
                    $guide->subestado = "Suspende Entrega";
                }
                if($guide->subestado ==="Transporte en espera de atención"){
                    $guide->subestado = "Transporte en espera de atención";
                }
                if($guide->subestado ==="Zona Restringida y/o Sin $guide->subestado"){
                    $guide->subestado = "Zona Restringida y/o Sin Referencia";
                }
                
                $chequear = $this->repository->checkReported($guide->cud, $guide->estado, $guide->subestado, $guide->idpedido_detalle);
                if ($chequear) {
                    Log::info('Ya se reporto esta guia ', ['CUD' => $guide->cud]);
                    continue;
                }

                $req_body = [
                    "CUD" => $guide->cud,
                    "Estado" => ucwords(strtolower($guide->estado)),
                    "SubEstado" => $guide->subestado,
                    "Placa" => $guide->numero_placa,
                    "Courier" => $guide->courier,
                    "Fecha" => $guide->fecha,
                    "NombreReceptor" => $guide->contacto_nombre_descarga,
                    "IDReceptor" => $guide->contacto_dni_descarga,
                    "TrackNumber" => $guide->nro_guia_sistema,
                    "URL" => $guide->url
                ];

                if (env('RIPLEY_INTEGRACION_API_SEND')) {
                    $cliente = new Client(['base_uri' => env('RIPLEY_INTEGRACION_API_URL')]);
                    
                    try {
                        $req = $cliente->request('POST', 'sendStateCourierOnline', [
                            "headers" => [
                                'x-api-key' => 'Me0qO1pYhg9VKMcfnWyVp1pMtyez8aNp2ZHg7EOi',
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                        Log::warning('Reportar estado a ripley, ', ['req' => $req_body, 'exception' => $response]);
                        $this->repository->LogInsert($guide->cud, $guide->estado, $guide->subestado, $guide->idpedido_detalle, 'ERROR', $req_body, $response);
                        continue;
                    }
                    catch (Exception $e) {
                        Log::error('Integracion ripley', ['cliente' => 'Ripley', 'exception' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'req' => $req_body]);
                        die();
                    }

                    $response = json_decode($req->getBody()->getContents());
                } else {
                    $response = 'test response';
                }

                $this->repository->LogInsert($guide->cud, $guide->estado, $guide->subestado, $guide->idpedido_detalle, 'SUCCESS', $req_body, $response);
            }
            
            $res['success'] = true;
            Log::info('Proceso de integracion con ripley exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion ripley', ['cliente' => 'Ripley', 'exception' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()]);
            $res['mensaje'] = $e->getMessage();    
        }
        return $res;
    }

    public function integracionOechsle()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuideOeschle();
            Log::info('Proceso de integracion con Oeschle', ['nro_registros' => count($guides)]);
            
            $req_body = [];
            foreach ($guides as $key => $guide) {
                $items = [];
                foreach ($guide->sku_product as $key => $product) {
                    array_push($items, [
                        'skuCode' => $product->sku_code,
                        'deliveredQuantity' => $product->sku_pieces
                    ]);
                }

                array_push($req_body, [
                    "companyCode" => "OE",
                    "dispatchNumber" => $guide->seg_code,
                    "items" => $items
                ]);
            }

            if (env('OESCHLE_INTEGRACION_API_SEND')) {
                $cliente = new Client(['base_uri' => env('OESCHLE_INTEGRACION_API_URL')]);
                    
                try {
                    $req = $cliente->request('POST', 'provider/delivery', [
                        "headers" => [
                            'client_id' => env('OESCHLE_INTEGRACION_API_KEY'),
                        ],
                        "json" => $req_body
                    ]);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                    Log::error('Reportar estado a Oechsle, ', ['req' => $req_body, 'exception' => $response]);
                    $this->repository->LogInsertOechsle('ERROR', $req_body, $response);
                    return $res;
                }
    
                $response = json_decode($req->getBody()->getContents());
                $this->repository->updateReportadoOeschle($guides);
            } else {
                $response = 'fake_response';
            }

            $this->repository->LogInsertOechsle('EXITO', $req_body, $response);

            $res['success'] = true;
            Log::info('Proceso de integracion con Oechsle exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Oechsle', ['cliente' => 'Oechsle', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();    
        }
        return $res;
    }
}
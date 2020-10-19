<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\ReporteRepository;
use Exception;
use Illuminate\Database\QueryException;
use Log;

class ReporteService
{
    protected $repository;
    public function __construct(ReporteRepository $reporteRepository) {
        $this->repository = $reporteRepository;
    }

    public function reporte_control($request)
    {
        try {

            $ruta = url('storage/reportes/');
            $data = $request->all();
            $data_reporte = $this->repository->sp_reporte_control($data['desde'], $data['hasta']);

            $fileName = date('YmdHis') . '_reporte_control_' . rand(1, 100) . '.csv';
            $handle = fopen('../storage/app/public/reportes/'.$fileName, 'w+');

            fputcsv($handle, [
                'CLIENTE', 'BARRA', 'CUD', 'NUMERO GUIA', 'FECHA PEDIDO', 'FECHA ENVIO',
                'NOMBRE CONDUCTOR','TIPO VEHICULO','PLACA','PROVEEODR','ESTADO ENVIO','DESTINATARIO','TELEFONO 1','TELEFONO 2',
                'DIRECCION','DEPARTAMENTO','DISTRITO','PROVINCIA','TIPO ZONA','FECHA ASIGNADO','ULTFECHA ESTADO',
                'ULT ESTADO', 'OBSERVACIONES', 'VISITA 1', 'RESULTADO 1', 'VISITA 2', 'RESULTADO 2', 'VISITA 3',
                'RESULTADO 3','CANT VISITAS','NRO IMAGENES'
            ]);

            foreach($data_reporte as $row) {
                fputcsv($handle, [
                    $row->org_name, $row->client_barcode, $row->seg_code, $row->guide_number, $row->fecha_guia, $row->fecha_envio, $row->driver_name,
                    $row->vehicle_type, $row->plate_number, $row->provider, $row->estado_envio, $row->client_name, $row->client_phone1, $row->client_phone2,
                    $row->address, $row->department, $row->district, $row->province, $row->zone_type, $row->fecha_asignado, $row->ultfecha_estado,
                    $row->ult_estado, $row->motive, $row->fecha_visita1, $row->visita1_status, $row->fecha_visita2, $row->visita2_status, $row->fecha_visita3,
                    $row->visita3_status,$row->cantidad_visitas,$row->nro_imagenes
                ]);
            }

            fclose($handle);


            Log::info('Generar reporte control', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte control', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte control', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte control', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }
}

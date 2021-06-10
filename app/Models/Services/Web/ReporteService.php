<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Exports\Reportes\ReporteCargaExport;
use App\Exports\Reportes\ReporteControlExport;
use App\Exports\Reportes\ReporteControlProveedorExport;
use App\Exports\Reportes\ReporteEficienciaExport;
use App\Exports\Reportes\ReporteImgMonitorExport;
use App\Exports\Reportes\ReporteRecoleccionExport;
use App\Helpers\QueryHelper;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\ReporteRepository;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use Log;
use Maatwebsite\Excel\Facades\Excel;

class ReporteService
{
    protected $repository;
    public function __construct(ReporteRepository $reporteRepository) {
        $this->repository = $reporteRepository;
    }

    public function reporte_control($request)
    {
        try {
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $data = $request->all();
            // $data_reporte = $this->repository->sp_reporte_control($data['desde'], $data['hasta'], $user->username);
            $fileName = date('YmdHis') . '_reporte_control_' . rand(1, 100) . '.xlsx';
            // $handle = fopen('../storage/app/public/reportes/'.$fileName, 'w+');
            Excel::store(new ReporteControlExport($user->username, $data['desde'], $data['hasta'], $data['type']), $fileName, 'reportes');

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

    public function reporte_torre_control($request)
    {
        try {

            $ruta = url('storage/reportes/');
            $data = $request->all();
            $data_reporte = $this->repository->sp_reporte_torre_control($data['desde'], $data['hasta']);

            $fileName = date('YmdHis') . '_reporte_torre_control_' . rand(1, 100) . '.csv';
            $handle = fopen('../storage/app/public/reportes/'.$fileName, 'w+');

            fputcsv($handle, [
                'CLIENTE', 'BARRA', 'CUD', 'NRO GUIA', 'FECHA PEDIDO',
                'ULT ESTADO', 'FECHA ASIGNADO', 'ESTADO ENVIO', 'ESTADO PEDIDO', 'OBSERVACIONES'
            ]);

            foreach($data_reporte as $row) {
                fputcsv($handle, [
                    $row->org_name, $row->client_barcode, $row->seg_code, $row->guide_number, $row->fecha_guia, $row->ult_estado,
                    $row->fecha_asignado, $row->envio_estado, $row->detalle_estado, $row->observaciones
                ]);
            }

            fclose($handle);


            Log::info('Generar reporte torre control', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte torre control', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte torre control', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte torre control', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function reporte_control_sku($request)
    {
        try {

            $ruta = url('storage/reportes/');
            $data = $request->all();
            $data_reporte = $this->repository->sp_reporte_control_sku($data['desde'], $data['hasta']);

            $fileName = date('YmdHis') . '_reporte_control_sku_' . rand(1, 100) . '.csv';
            $handle = fopen('../storage/app/public/reportes/'.$fileName, 'w+');

            fputcsv($handle, [
                'CLIENTE', 'BARRA', 'CUD', 'NUMERO GUIA', 'CODIGO SKU', 'SKU DESCRIPCION', 'FECHA PEDIDO', 'FECHA ENVIO',
                'NOMBRE CONDUCTOR', 'TIPO VEHICULO', 'PLACA', 'PROVEEODR', 'ESTADO ENVIO', 'DESTINATARIO', 'TELEFONO 1', 'TELEFONO 2',
                'DIRECCION', 'DEPARTAMENTO', 'DISTRITO', 'PROVINCIA', 'TIPO ZONA', 'FECHA ASIGNADO', 'ULTFECHA ESTADO', 'ULT ESTADO',
                'OBSERVACIONES', 'VISITA 1', 'RESULTADO 1', 'VISITA 2', 'RESULTADO 2', 'VISITA 3', 'RESULTADO 3',
                'CANT VISITAS', 'NRO IMAGENES'
            ]);

            foreach($data_reporte as $row) {
                fputcsv($handle, [
                    $row->org_name, $row->client_barcode, $row->seg_code, $row->guide_number, $row->sku_code, $row->sku_description, $row->fecha_guia, $row->fecha_envio, $row->driver_name,
                    $row->vehicle_type, $row->plate_number, $row->provider, $row->estado_envio, $row->client_name, $row->client_phone1, $row->client_phone2, $row->address, $row->department,
                    $row->district, $row->province, $row->zone_type, $row->fecha_asignado, $row->ultfecha_estado, $row->ult_estado, $row->motive, $row->fecha_visita1, $row->visita1_status,
                    $row->fecha_visita2, $row->visita2_status, $row->fecha_visita3, $row->visita3_status, $row->cantidad_visitas, $row->nro_imagenes
                ]);
            }

            fclose($handle);

            Log::info('Generar reporte control sku', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte control sku', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte control sku', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte control sku', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function control_proveedor($request)
    {
        try {
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $data = $request->all();
            // $data_reporte = $this->repository->sp_reporte_control($data['desde'], $data['hasta'], $user->username);
            $fileName = date('YmdHis') . '_reporte_control_proveedor_' . rand(1, 100) . '.xlsx';
            $handle = fopen('../storage/app/public/reportes/'.$fileName, 'w+');
            Excel::store(new ReporteControlProveedorExport($user->username, $data['desde'], $data['hasta'], $data['type']), $fileName, 'reportes');

            Log::info('Generar reporte control proveedor', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte control proveedor', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte control proveedor', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte control proveedor', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function img_monitor($request)
    {
        try {
            $filtros = $request->get('filters');
            $origen = $request->get('origin');
            $daterange = $request->get('daterange');
            $where = QueryHelper::generarFiltro($filtros, $origen, $daterange);
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $fileName = date('YmdHis') . '_reporte_img_monitor_' . rand(1, 100) . '.xlsx';
            Excel::store(new ReporteImgMonitorExport($user->username, $where), $fileName, 'reportes');
            
            Log::info('Generar reporte monitor imagenes', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte monitor imagenes', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte monitor imagenes', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte monitor imagenes', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function reporte_recoleccion($request)
    {
        try {
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $data = $request->all();
            $fileName = date('YmdHis') . '_reporte_recoleccion_' . rand(1, 100) . '.xlsx';
            Excel::store(new ReporteRecoleccionExport($user->username, $data['desde'], $data['hasta']), $fileName, 'reportes');

            Log::info('Generar reporte recoleccion', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte recoleccion', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte recoleccion', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte recoleccion', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function reporte_eficiencia($request)
    {
        try {
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $data = $request->all();
            $fileName = date('YmdHis') . '_reporte_eficiencia_' . rand(1, 100) . '.xlsx';
            Excel::store(new ReporteEficienciaExport($user->username, $data['desde'], $data['hasta'], $data['id_corporation'], $data['id_organization']), $fileName, 'reportes');

            Log::info('Generar reporte eficiencia', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte eficiencia', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte eficiencia', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte eficiencia', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }

    public function reporte_data_carga($request)
    {
        try {
            $user = auth()->user();
            $ruta = url('storage/reportes/');
            $data = $request->all();
            $fileName = date('YmdHis') . '_reporte_data_carga_' . rand(1, 100) . '.xlsx';
            Excel::store(new ReporteCargaExport($data['desde'], $data['hasta'], $data['id_corporation'], $data['id_organization']), $fileName, 'reportes');

            Log::info('Generar reporte data carga', ['request' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Generar reporte data carga', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Generar reporte data carga', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Generar reporte data carga', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['reporte' => $ruta .'/'. $fileName]);
    }
}

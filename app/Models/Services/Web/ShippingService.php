<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Models\Functions\FunctionModel;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\ShippingRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mumpo\FpdfBarcode\FpdfBarcode;
use Intervention\Image\Facades\Image;
use App\Models\Services\Web\CustomPDF;
use Carbon\Carbon;

class ShippingService
{
    protected $repo;
    public function __construct(ShippingRepository $shippingRepository)
    {
        $this->repo = $shippingRepository;
    }

    public function index()
    {
    }

    public function grabarImagen($request)
    {
        try {
            $guide = $this->repo->getShippingDetailByGuideNumber($request->get('guide_number'), $request->get('id_shipping_order'));
            if (!count($guide)) {
                throw new CustomException(['Detalle no encontrado.', 2010], 400);
            } 
            // elseif ($guide[0]->status !== 'CURSO') {
            //     throw new CustomException(['La guia no se encuentra en Curso.', 2011], 400);
            // }
            $destination_path = Storage::disk('imagenes')->getAdapter()->getPathPrefix() . $guide[0]->id_guide;
            # CHeck if folder exists before create one
            if (!file_exists($destination_path)) {
                File::makeDirectory($destination_path, $mode = 0777, true, true);
                File::makeDirectory($destination_path . '/thumbnail', $mode = 0777, true, true);
            }

            $imagen = $request->file('imagen');
            $nombre_imagen = $guide[0]->id_guide . '_' . time() . '.jpg';
            $thumbnail = Image::make($imagen->getRealPath());

            # Guardamos el thumnail primero
            $thumbnail->resize(250, 250, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . '/thumbnail/' . $nombre_imagen);

            # Redimesionamos la imagen a 720x720
            $resize = Image::make($imagen->getRealPath());
            $resize->resize(720, 720, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . '/' . $nombre_imagen);

            $ruta = url('storage/imagenes2/' . $guide[0]->id_guide . '/' . $nombre_imagen);
            foreach ($guide as $key => $gd) {
                $this->repo->insertarImagen($gd->id_guide, $gd->id_shipping_order, $ruta, $request->get('descripcion'), $request->get('tipo_imagen'));
            }

            Log::info('Grabar imagen exitoso', ['request' => $request->except('imagen'), 'nombre_imagen' => $ruta]);
        } catch (CustomException $e) {
            Log::warning('Grabar imagen', ['expcetion' => $e->getData()[0], 'request' => $request->except('imagen')]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Grabar imagen', ['expcetion' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Grabar imagen', ['exception' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['mensaje' => 'Imagen guardada con exito']);
    }

    public function print_hoja_ruta($request)
    {
        $data = $request->all();

        $hoja_ruta = $this->repo->get_hoja_ruta($data['id_shipping_order']);
        $disk = Storage::disk('hoja_ruta');
        $ruta = url('storage/hoja_ruta/');
        $file_exists = (Storage::disk('hoja_ruta')->exists($hoja_ruta->hoja_ruta_doc));

        if (!$hoja_ruta->hoja_ruta_doc || !$file_exists) {
            $data_shipping = $this->repo->get_imprimir_hoja_ruta($data['id_shipping_order']);
            $res = $this->crear_hoja_ruta($data_shipping);
            $this->repo->actualizar_hoja_ruta($res['file_name'], $data['id_shipping_order']);
            $hoja_ruta->hoja_ruta_doc = $res['file_name'];
        }

        return Res::success(['hoja_ruta' => $ruta .'/'. $hoja_ruta->hoja_ruta_doc]);
    }

    public function crear_hoja_ruta($data)
    {
        try {
            $pdf = new CustomPDF();
            $cellMargin = 2 * 1.000125;
            $lmargin = 5;
            $rmargin = 5;
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetMargins($lmargin, $rmargin);
            $pdf->Ln(0);
            $pdf->SetFont('Times', '', 10);
            $y = $pdf->GetY();
            $pdf->MultiCell(16, 5, 'Empresa: ', 0, 'L');
            $pdf->SetXY($lmargin + 16, $y);
            $pdf->MultiCell(61, 5, utf8_decode($data[0]->provider_name), 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(35, 5, 'Fecha de Asignacion:', 0, 'L');
            $pdf->SetXY($lmargin + 112, $y);
            $pdf->MultiCell(20, 5, Carbon::createFromFormat('Y-m-d H:i:s', $data[0]->date_created)->format('Y-m-d'), 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(20, 5, 'Conductor: ', 0, 'L');
            $pdf->SetXY($lmargin + 20, $y);
            $pdf->MultiCell(57, 5, utf8_decode($data[0]->first_name .' '. $data[0]->last_name), 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(11, 5, 'Placa:', 0, 'L');
            $pdf->SetXY($lmargin + 88, $y);
            $pdf->MultiCell(20, 5, utf8_decode($data[0]->plate_number), 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(22, 5, 'Cod. Envio: ', 0, 'L');
            $pdf->SetXY($lmargin + 22, $y);
            $pdf->MultiCell(55, 5, $data[0]->id_shipping_order, 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(22, 5, 'Total Guias:', 0, 'L');
            $pdf->SetXY($lmargin + 99, $y);
            $uniqueCount = count(array_unique(array_column($data, 'guide_number')));
            $pdf->MultiCell(10, 5, $uniqueCount, 0, 'L');

            $pdf->SetXY($lmargin + 109, $y);
            $pdf->MultiCell(22, 5, 'Total Bultos:', 0, 'L');
            $pdf->SetXY($lmargin + 133, $y);
            $sum = 0;
            foreach ($data as $item) {
                $sum += $item->nro_guias;
            }
            $pdf->MultiCell(10, 5, $sum, 0, 'L');
            
            $y = $pdf->GetY();
    
            $pdf->MultiCell(29, 5, 'Hora de Llegada: ', 0, 'L');
            $pdf->SetXY($lmargin + 29, $y);
            $pdf->MultiCell(48, 5, '______ : _______', 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(26, 5, 'Hora de Salida:', 0, 'L');
            $pdf->SetXY($lmargin + 103, $y);
            $pdf->MultiCell(29, 5, '______ : _______', 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(29, 5, 'Nro de Celular:', 0, 'L');
            $pdf->SetXY($lmargin + 29, $y);
            $pdf->MultiCell(48, 5, '__________________', 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(30, 5, utf8_decode('Batería del Celular:'), 0, 'L');
            $pdf->SetXY($lmargin + 107, $y);
            $pdf->MultiCell(33, 5, '_________________', 0, 'L');
            $y = $pdf->GetY();

            $pdf->MultiCell(32, 5, 'Hora Asignacion:', 0, 'L');
            $pdf->SetXY($lmargin + 32, $y);
            // $pdf->MultiCell(48, 5, Carbon::createFromFormat('H:i:s', $data[0]->date_created)->format('Y-m-d'), 0, 'L');
            $pdf->MultiCell(48, 5, Carbon::createFromFormat('Y-m-d H:i:s', $data[0]->date_created)->format('Y-m-d'), 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(30, 5, utf8_decode('Total direcciones:'), 0, 'L');
            $pdf->SetXY($lmargin + 107, $y);
            $uniqueAddress = count(array_unique(array_column($data, 'address')));
            $pdf->MultiCell(33, 5, $uniqueAddress, 0, 'L');
            $y = $pdf->GetY();

            $pdf->code128(150, 13, str_pad($data[0]->id_shipping_order, 7, "0", STR_PAD_LEFT) , 50, 20, false);
            $pdf->Ln(2);
    
            $pdf->SetDrawColor(150, 153, 141);
            $pdf->Line(10, 41, 195, 41);
            $pdf->Ln(2);
            $y = $pdf->GetY();
    
            // total largo pagina 210
            $pdf->SetX(10);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetDrawColor(69, 69, 69);
            $pdf->MultiCell(90, 10, 'Nombres del auxiliar:', 1, 'L');
            $pdf->SetXY($lmargin + 95, $y);
            $pdf->MultiCell(50, 10, 'DNI:', 1, 'L');
            $pdf->SetXY($lmargin + 145, $y);
            $pdf->MultiCell(45, 10, 'Firma:', 1, 'L');
            $y = $pdf->GetY();
    
            $pdf->SetX(10);
            $pdf->MultiCell(90, 10, 'Lider que manifesto:', 1, 'L');
            $pdf->SetXY($lmargin + 95, $y);
            $pdf->MultiCell(50, 10, 'Firma Lider:', 1, 'L');
            $pdf->SetXY($lmargin + 145, $y);
            $pdf->MultiCell(45, 10, 'Apoyo:', 1, 'L');
            $y = $pdf->GetY();
    
            // $pdf->SetDrawColor(150,153,141);
            // $pdf->Line(10,$y+2, 195,$y+2);
            $pdf->Ln(4);
            $y = $pdf->GetY();
    
            $pdf->SetX(10);
            $pdf->SetDrawColor(69, 69, 69);
            $pdf->MultiCell(47, 10, 'ENTREGAS:', 1, 'L');
            $pdf->SetXY($lmargin + 52, $y);
            $pdf->MultiCell(43, 10, 'REZAGOS:', 1, 'L');
            $pdf->SetXY($lmargin + 95, $y);
            $pdf->MultiCell(50, 10, 'AUSENTES:', 1, 'L');
            $pdf->SetXY($lmargin + 145, $y);
            $pdf->MultiCell(45, 10, 'H. RETORNO:', 1, 'L');
            $y = $pdf->GetY();
    
            $pdf->SetX(10);
            $pdf->MultiCell(90, 10, 'Lider que descargo:', 1, 'L');
            $pdf->SetXY($lmargin + 95, $y);
            $pdf->MultiCell(50, 10, 'Firma:', 1, 'L');
            $pdf->SetXY($lmargin + 145, $y);
            $pdf->MultiCell(45, 10, 'Fotos (SI/NO):', 1, 'L');
            $y = $pdf->GetY();
    
            $pdf->SetDrawColor(150, 153, 141);
            $pdf->Line(10, $y + 2, 195, $y + 2);
            $pdf->Ln(4);
            $y = $pdf->GetY();
    
            $pdf->SetDrawColor(69, 69, 69);
            $pdf->MultiCell(8, 6, 'Nro', 1, 'L');
            $pdf->SetXY($lmargin + 8, $y);
            $pdf->MultiCell(38, 6, 'Codigo de Barra', 1, 'L');
            $pdf->SetXY($lmargin + 46, $y);
            $pdf->MultiCell(30, 6, 'Nro Guia', 1, 'L');
            $pdf->SetXY($lmargin + 76, $y);
            $pdf->MultiCell(33, 6, 'Distrito', 1, 'L');
            $pdf->SetXY($lmargin + 109, $y);
            $pdf->MultiCell(80, 6, utf8_decode('Direccion de Entrega'), 1, 'L');
            $pdf->SetXY($lmargin + 189, $y);
            $pdf->MultiCell(10, 6, 'Bults', 1, 'L');
            $y = $pdf->GetY();
    
            $pdf->SetFont('Times', '', 10);
    
            $filas = 1;
            $pagina = 1;
            foreach ($data as $key => $value) {


                if( in_array($filas, [35,36,37]) && $pagina === 1 )
                {
                    $pdf->AddPage();
                    $pdf->Ln(4);
                    $y = $pdf->GetY();
                    $filas = 1;
                    $pagina += 1;
                }

                if ( in_array($filas, [53,54,55]) && $pagina > 1 ) {
                    $pdf->AddPage();
                    $pdf->Ln(4);
                    $y = $pdf->GetY();
                    $filas = 1;
                }

                $direccion = utf8_decode(ucwords(strtolower($value->address)));
                $distrito = utf8_decode(ucwords(strtolower($value->district)));
                $width_dir = $pdf->GetStringWidth($direccion);
                $width_dis = $pdf->GetStringWidth($distrito);
                $distrito_row = ceil($width_dis / (35 - $cellMargin));
                $direccion_row = ceil($width_dir / (82 - $cellMargin));
                $rows = max($distrito_row, $direccion_row);
        
                $pdf->SetDrawColor(69, 69, 69);
                $pdf->MultiCell(8, 5 * $rows, $key+1, 1, 'C');
                $pdf->SetXY($lmargin + 8, $y);
                $pdf->MultiCell(38, 5 * $rows, $value->client_barcode, 1, 'L');
                $pdf->SetXY($lmargin + 46, $y);
                $pdf->MultiCell(30, 5 * $rows, $value->guide_number, 1, 'L');
                $pdf->SetXY($lmargin + 76, $y);
                $pdf->MultiCell(33, ($distrito_row >= $direccion_row) ? 5 : 5 * $rows, $distrito, 1, 'L');
                $pdf->SetXY($lmargin + 109, $y);
                $pdf->MultiCell(80, ($direccion_row >= $distrito_row) ? 5 : 5 * $rows, $direccion, 1, 'L');
                $pdf->SetXY($lmargin + 189, $y);
                $pdf->MultiCell(10, 5 * $rows, $value->nro_guias, 1, 'L');
                $y = $pdf->GetY();

                $filas += $rows;
            }

            $pdf->SetFont('Times', '', 8);
            $pdf->SetDrawColor(150, 153, 141);
            $pdf->Line(10, $y + 2, 195, $y + 2);
            $pdf->Ln(4);
            $y = $pdf->GetY();
    
            // $pdf->SetDrawColor(69, 69, 69);
            // $pdf->MultiCell(198, 6, utf8_decode('Transportista:'), 1, 'L');
            // $y = $pdf->GetY();
            // $pdf->MultiCell(198, 6, utf8_decode('Nombres y Apellidos:'), 1, 'L');
            // $y = $pdf->GetY();
            // $pdf->MultiCell(198, 6, utf8_decode('DNI:'), 1, 'L');
            // $y = $pdf->GetY();
    
            // $pdf->SetDrawColor(150, 153, 141);
            // $pdf->Line(10, $y + 2, 195, $y + 2);
            // $pdf->Ln(4);
            // $y = $pdf->GetY();
    
            // $pdf->SetDrawColor(69, 69, 69);
            // $pdf->MultiCell(120, 40, utf8_decode(''), 1, 'L');
            // $pdf->SetXY($lmargin + 120, $y);
            // $pdf->MultiCell(78, 40, '', 1, 'L');
            // $y = $pdf->GetY();
    
            // $pdf->Text(22, $y - 10, '____________________________________________________________');
            // $pdf->Text(55, $y - 5, 'FIRMA');
    
            // $pdf->Text(153, $y - 5, 'HUELLA DACTILAR');

            $disk = Storage::disk('hoja_ruta');
            $fileName = date('YmdHis') . '_cc_' . '51616516' . '_' . rand(1, 100) . '.pdf';
            $save = $disk->put($fileName, $pdf->Output('S', '', true));
            if (!$save) {
                throw new Exception('No se pudo grabar la hoja de ruta');
            }
            $res['file_name'] = $fileName;

            return $res;

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}

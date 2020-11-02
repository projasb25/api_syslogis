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
use Mumpo\FpdfBarcode\FpdfBarcode;
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

    public function print_hoja_ruta($request)
    {
        $data = $request->all();

        $hoja_ruta = $this->repo->get_hoja_ruta($data['id_shipping_order']);
        $disk = Storage::disk('hoja_ruta');
        $ruta = url('storage/hoja_ruta/');

        if(!$hoja_ruta->hoja_ruta_doc){
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
            $pdf->SetFont('Times', '', 8);
            $y = $pdf->GetY();
            $pdf->MultiCell(13, 5, 'Empresa: ', 0, 'L');
            $pdf->SetXY($lmargin + 13, $y);
            $pdf->MultiCell(64, 5, utf8_decode($data[0]->provider_name), 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(27, 5, 'Fecha de Asignacion:', 0, 'L');
            $pdf->SetXY($lmargin + 104, $y);
            $pdf->MultiCell(20, 5, Carbon::createFromFormat('Y-m-d H:i:s', $data[0]->date_created)->format('Y-m-d'), 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(14, 5, 'Conductor: ', 0, 'L');
            $pdf->SetXY($lmargin + 14, $y);
            $pdf->MultiCell(63, 5, utf8_decode($data[0]->first_name .' '. $data[0]->last_name), 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(10, 5, 'Placa:', 0, 'L');
            $pdf->SetXY($lmargin + 87, $y);
            $pdf->MultiCell(20, 5, utf8_decode($data[0]->plate_number), 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(16, 5, 'Cod. Envio: ', 0, 'L');
            $pdf->SetXY($lmargin + 16, $y);
            $pdf->MultiCell(61, 5, $data[0]->id_shipping_order, 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(15, 5, 'Total Guias:', 0, 'L');
            $pdf->SetXY($lmargin + 92, $y);
            $uniqueCount = count(array_unique(array_column($data, 'guide_number'))); 
            $pdf->MultiCell(13, 5, $uniqueCount, 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(24, 5, 'Hora de Llegada: ', 0, 'L');
            $pdf->SetXY($lmargin + 24, $y);
            $pdf->MultiCell(53, 5, '______ : _______', 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(21, 5, 'Hora de Salida:', 0, 'L');
            $pdf->SetXY($lmargin + 98, $y);
            $pdf->MultiCell(23, 5, '______ : _______', 0, 'L');
            $y = $pdf->GetY();
    
            $pdf->MultiCell(26, 5, 'Numero de Celular:', 0, 'L');
            $pdf->SetXY($lmargin + 26, $y);
            $pdf->MultiCell(51, 5, '__________________', 0, 'L');
            $pdf->SetXY($lmargin + 77, $y);
            $pdf->MultiCell(25, 5, utf8_decode('Batería del Celular:'), 0, 'L');
            $pdf->SetXY($lmargin + 102, $y);
            $pdf->MultiCell(30, 5, '__________________', 0, 'L');
            $y = $pdf->GetY();
            $pdf->code128(150, 13, str_pad($data[0]->id_shipping_order, 7, "0", STR_PAD_LEFT) , 50, 20, false);
            $pdf->Ln(2);
    
            $pdf->SetDrawColor(150, 153, 141);
            $pdf->Line(10, 39, 195, 39);
            $pdf->Ln(4);
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
            $pdf->MultiCell(10, 6, 'Nro', 1, 'L');
            $pdf->SetXY($lmargin + 10, $y);
            $pdf->MultiCell(28, 6, 'Codigo de Barra', 1, 'L');
            $pdf->SetXY($lmargin + 38, $y);
            $pdf->MultiCell(35, 6, 'Distrito', 1, 'L');
            $pdf->SetXY($lmargin + 73, $y);
            $pdf->MultiCell(115, 6, utf8_decode('Direccion de Entrega'), 1, 'L');
            $pdf->SetXY($lmargin + 188, $y);
            $pdf->MultiCell(10, 6, 'Bultos', 1, 'L');
            $y = $pdf->GetY();
    
            $pdf->SetFont('Times', '', 7);
    
            $filas = 0;
            foreach ($data as $key => $value) {

                if ($filas % 44 === 0 && $filas!== 0) {
                    $pdf->AddPage();
                    $y = $pdf->GetY();
                }
                $direccion = $value->address;
                $distrito = $value->district;
                $width_dir = $pdf->GetStringWidth($direccion);
                $width_dis = $pdf->GetStringWidth($distrito);
                $distrito_row = ceil($width_dis / (35 - $cellMargin));
                $direccion_row = ceil($width_dir / (125 - $cellMargin));
                $rows = max($distrito_row, $direccion_row);
        
                $pdf->SetDrawColor(69, 69, 69);
                $pdf->MultiCell(10, 4 * $rows, $key+1, 1, 'C');
                $pdf->SetXY($lmargin + 10, $y);
                $pdf->MultiCell(28, 4 * $rows, $value->client_barcode, 1, 'L');
                $pdf->SetXY($lmargin + 38, $y);
                $pdf->MultiCell(35, ($distrito_row > $direccion_row) ? 4 : 4 * $rows, $distrito, 1, 'L');
                $pdf->SetXY($lmargin + 73, $y);
                $pdf->MultiCell(115, ($direccion_row > $distrito_row) ? 4 : 4 * $rows, utf8_decode($direccion), 1, 'L');
                $pdf->SetXY($lmargin + 188, $y);
                $pdf->MultiCell(10, 4 * $rows, $value->nro_guias, 1, 'L');
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

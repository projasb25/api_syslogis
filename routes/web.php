<?php

use App\Models\Services\Web\CustomPDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mumpo\FpdfBarcode\Fpdf;
use Mumpo\FpdfBarcode\FpdfBarcode;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('x', function(){
    $pdf = PDF::loadView('pdf.orden_compra.detalle');
    $pdf->setOption('margin-bottom', 0);
    $pdf->setOption('margin-top',30);
    return $pdf->stream('oc.pdf');
});

Route::get('pdf', function () {
    $pdf = new FpdfBarcode();
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
    $pdf->MultiCell(64, 5, '$variable empresa', 0, 'L');
    $pdf->SetXY($lmargin + 77, $y);
    $pdf->MultiCell(27, 5, 'Fecha de Asignacion:', 0, 'L');
    $pdf->SetXY($lmargin + 104, $y);
    $pdf->MultiCell(20, 5, '2020/12/12', 0, 'L');
    $y = $pdf->GetY();

    $pdf->MultiCell(14, 5, 'Conductor: ', 0, 'L');
    $pdf->SetXY($lmargin + 14, $y);
    $pdf->MultiCell(63, 5, 'ICA PICKER', 0, 'L');
    $pdf->SetXY($lmargin + 77, $y);
    $pdf->MultiCell(10, 5, 'Placa:', 0, 'L');
    $pdf->SetXY($lmargin + 87, $y);
    $pdf->MultiCell(20, 5, 'ICA-123', 0, 'L');
    $y = $pdf->GetY();

    $pdf->MultiCell(16, 5, 'Cod. Envio: ', 0, 'L');
    $pdf->SetXY($lmargin + 16, $y);
    $pdf->MultiCell(61, 5, '29511', 0, 'L');
    $pdf->SetXY($lmargin + 77, $y);
    $pdf->MultiCell(10, 5, 'Total:', 0, 'L');
    $pdf->SetXY($lmargin + 87, $y);
    $pdf->MultiCell(20, 5, '5', 0, 'L');
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
    $pdf->code128(150, 13, '201324092503', 50, 20, false);
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
    $pdf->MultiCell(35, 6, 'Codigo Sistema', 1, 'L');
    $pdf->SetXY($lmargin + 45, $y);
    $pdf->MultiCell(28, 6, 'Distrito', 1, 'L');
    $pdf->SetXY($lmargin + 73, $y);
    $pdf->MultiCell(125, 6, utf8_decode('Direccion de Entrega'), 1, 'L');
    $y = $pdf->GetY();



    // foreach ($variable as $key => $value) {
    $direccion = 'urbanizacion los huarangos 4ta etapaasdfmz';
    $distrito = 'SANTO DOMINGOasdfasdf';
    $width_dir = $pdf->GetStringWidth($direccion);
    $width_dis = $pdf->GetStringWidth($distrito);
    $distrito_row = ceil($width_dis / (28 - $cellMargin));
    $direccion_row = ceil($width_dir / (125 - $cellMargin));
    $rows = max($distrito_row, $direccion_row);

    $pdf->SetDrawColor(69, 69, 69);
    $pdf->MultiCell(10, 5 * $rows, '1', 1, 'C');
    $pdf->SetXY($lmargin + 10, $y);
    $pdf->MultiCell(35, 5 * $rows, '20209000000614666', 1, 'L');
    $pdf->SetXY($lmargin + 45, $y);
    $pdf->MultiCell(28, ($distrito_row > $direccion_row) ? 5 : 5 * $rows, $distrito . ' ' . $rows, 1, 'L');
    $pdf->SetXY($lmargin + 73, $y);
    $pdf->MultiCell(125, ($direccion_row > $distrito_row) ? 5 : 5 * $rows, utf8_decode($direccion), 1, 'L');
    $y = $pdf->GetY();

    // end foreach
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

    $pdf->Output();
    exit;
});

class pdftest extends FpdfBarcode
{

    function TextWithDirection($x, $y, $txt, $direction = 'R')
    {
        if ($direction == 'R')
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 1, 0, 0, 1, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        elseif ($direction == 'L')
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', -1, 0, 0, -1, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        elseif ($direction == 'U')
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, 1, -1, 0, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        elseif ($direction == 'D')
            $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', 0, -1, 1, 0, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        else
            $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->ColorFlag)
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        $this->_out($s);
    }
}

Route::get('pdf2', function () {
    $pdf = new pdftest();
    $cellMargin = 2 * 1.000125;
    $lmargin = 5;
    $rmargin = 5;
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetMargins($lmargin, $rmargin);
    $pdf->Ln(0);
    $pdf->SetFont('Times', '', 7);
    $y = $pdf->GetY();
    $pdf->SetAutoPageBreak(false);
    // var_dump($pdf->_getpagesize('a4')); (210.00155555556) - (297.00008333333) 

    // 297 - 10 (margin xy) 
    // 280 / 3 = 93
    // 90


    $box_x = 5;
    $box_y = 5;
    for ($i = 0; $i < 9; $i++) {
        if ($i  % 4 == 0 && $i != 0) {
            $pdf->AddPage();
            $box_y = 5;
        }
        // cuadro principal
        $pdf->Rect($box_x, $box_y, 190, 59);

        // cuadro 1.1 REMITENTE
            //header
            $pdf->Rect($box_x + 1, $box_y + 1, 6, 28);
            $pdf->SetFont('Times', 'B', 6);
            $pdf->TextWithDirection($box_x + 5, $box_y + 21, 'REMITENTE', 'U');

            // body
            $pdf->Rect($box_x + 7, $box_y + 1, 90, 28);
            $pdf->SetFont('Times', '', 6);
            $pdf->SetXY($box_x+8, $box_y + 1);
            $pdf->MultiCell(89,4,'NOMBRE: $adfajlsdfjlkasjdf',0,'J');
            $pdf->SetX($box_x+8);
            $pdf->MultiCell(89,4,'CIUDAD: $asdfasdfasdfasdfasdfasdfasdfasd',0,'J');
            $pdf->SetX($box_x+8);
            $pdf->MultiCell(89,4,'FECHA: $2012/02/05',0,'J');
            $pdf->SetX($box_x+8);
            $pdf->MultiCell(89,4,'SEGUIMIENTO: $201324099256651616',0,'J');
            $pdf->SetX($box_x+8);
            $pdf->MultiCell(89,4,'COD.ALTERNO: $201324099256651616',0,'J');
            $pdf->SetX($box_x+8);
            $pdf->MultiCell(89,4,'DIRECCION: $asdfasdfasdfasdf ASDF ASDFASDFASDFASDF asdfas dfasd fasdfasdfasdfasd fsdfasdfasdfasd',0,'L');

        // codigo de barra
            $pdf->code128($box_x + 20, ($box_y + 28 + 2), '201324092503', 50, 12, false);
            $pdf->Ln(2);
        
        // cuadro 2.1 DATOS
            //header
            $pdf->Rect($box_x + 1, ($box_y + 41 + 2), 6, 9);
            $pdf->SetFont('Times', 'B', 6);
            // $pdf->MultiCell(100,5,'asdfasdf',1,'J');
            $pdf->TextWithDirection($box_x + 5, $box_y + 51, 'DATOS', 'U');
            
            // body
            $pdf->Rect($box_x + 7, ($box_y + 41 + 2), 90, 9);
            $pdf->SetFont('Times', 'B', 10);
            $pdf->SetXY($box_x+8, ($box_y + 44 + 2));
            $pdf->MultiCell(45,4,'NRO. DE PIEZAS: $14',0,'J');
            $pdf->SetXY($box_x+8+45, ($box_y + 44 + 2));
            $pdf->MultiCell(45,4,'PESO SECO: $14',0,'J');
            $pdf->Line($box_x+8+41, ($box_y + 41 + 2), $box_x+8+41, ($box_y + 50 + 2));
            
            $pdf->SetX($box_x+8);

        // // cuadro 3.1 DATOS DE ENTREGA
        //     //header
        //     $pdf->Rect($box_x + 1, ($box_y + 52 + 2) , 6, 37);
        //     $pdf->SetFont('Times', 'B', 6);
        //     $pdf->TextWithDirection($box_x + 5, $box_y + 83, 'DATOS DE ENTREGA', 'U');

        //     // body
        //     $pdf->Rect($box_x + 7, ($box_y + 52 + 2), 90, 37);
        //     $pdf->SetFont('Times', '', 6);
        //     $pdf->SetXY($box_x+8, ($box_y + 57 + 2));
        //     $pdf->MultiCell(89,6,'FIRMA:  ____________________________________________________________',0,'J');
        //     $pdf->SetX($box_x+8);
        //     $pdf->MultiCell(89,6,'NOMBRE:  _________________________________________________________',0,'J');
        //     $pdf->SetX($box_x+8);
        //     $pdf->MultiCell(89,6,'VINCULO:  _________________________________________________________',0,'J');
        //     $pdf->SetX($box_x+8);
        //     $pdf->MultiCell(89,6,'DNI:  __________________________________',0,'J');
        //     $pdf->SetX($box_x+8);
        //     $pdf->Cell(44,6,'FECHA: _______ / _______ / _______',0,0,'L');
        //     $pdf->Cell(44,6,'Hora: __________________',0,0,'L');
        //     $pdf->SetX($box_x+8);

        // cuadro 1.2 DESTINATARIO
            //header
            $pdf->Rect($box_x + 99, $box_y + 1, 6, 19);
            $pdf->SetFont('Times', 'B', 6);
            $pdf->TextWithDirection($box_x + 99 + 4, $box_y + 19, 'DESTINATARIO', 'U');

            // body
            $pdf->Rect($box_x + 99 + 6, $box_y + 1, 84, 19);
            $pdf->SetFont('Times', '', 6);
            $pdf->SetXY($box_x + 99 + 7, $box_y + 1);
            $pdf->MultiCell(89,4,'NOMBRE: $adfajlsdfjlkasjdf',0,'J');
            $pdf->SetX($box_x + 99 + 7);
            $pdf->MultiCell(89,4,'CIUDAD: $asdfasdfasdfasdfasdfasdfasdfasd',0,'J');
            $pdf->SetX($box_x + 99 + 7);
            $pdf->Cell(27,4,'TELEFONO: $98291115',0,0,'L');
            $pdf->Cell(52,4,'EMAIL: $projas@gmasfic.comsdfasf',0,1,'L'); //lower to space
            $pdf->SetX($box_x + 99 + 7);
            $pdf->MultiCell(89,4,'DIRECCION: $asdfasdfasdfasdf ASDF ASDFASDFASDFASDF asdfas dfasd fasdfasdfasdfasd fsdfasdfasdfasd',0,'L');

        // cuadro 2.2 CONTENIDOs
            //header
            $pdf->Rect($box_x + 99, $box_y + 21, 6, 37);
            $pdf->SetFont('Times', 'B', 7);
            $pdf->TextWithDirection($box_x + 99 + 4, $box_y + 47, 'CONTENIDO', 'U');

            // body
            $pdf->Rect($box_x + 99 + 6, $box_y + 21, 84, 37);
            // foreach ($SKU_PRODUCTOS as $key => $value) {
                $pdf->SetFont('Times', 'B', 6);
                $pdf->SetXY($box_x + 99 + 7, $box_y + 22);
                $pdf->MultiCell(89,4,'NOMBRE: $adfajlsdfjlkasjdf',0,'J');
                $pdf->SetX($box_x + 99 + 7);
                $pdf->MultiCell(89,4,'CIUDAD: $asdfasdfasdfasdfasdfasdfasdfasd',0,'J');
                $pdf->SetX($box_x + 99 + 7);
                $pdf->Cell(27,4,'TELEFONO: $98291115',0,0,'L');
                $pdf->Cell(52,4,'EMAIL: $ASDFASDFAS@CASIDFADF.COM',0,1,'L');
                $pdf->SetX($box_x + 99 + 7);
                $pdf->MultiCell(89,4,'DIRECCION: $afasdsdfasd fsdfasdfasdfasd',0,'L');
            // }

        // // cuadro 2.3 OBSERVACIONES
        //     //header
        //     // $pdf->Rect($box_x + 99, $box_y + 66, 6, 25);
        //     $pdf->SetFont('Times', 'B', 6);
        //     $pdf->SetXY($box_x + 92 + 7, $box_y + 59);
        //     $pdf->Cell(26,4,'PRIMERA VISITA',1,0,'L');
        //     $pdf->Cell(26,4,'SEGUNDA VISITA',1,0,'L');
        //     $pdf->Cell(4,4,'1',1,0,'L');
        //     $pdf->Cell(4,4,'2',1,0,'L');
        //     $pdf->Cell(30,4,'MOTIVO DE REZAGO',1,1,'L');

        //     $pdf->SetX($box_x + 92 + 7);
        //     $pdf->Cell(26,4,'FECHA:',1,0,'L');
        //     $pdf->Cell(26,4,'FECHA:',1,1,'L');
        //     // $pdf->Cell(4,4,'',1,0,'L');
        //     // $pdf->Cell(4,4,'',1,1,'L');

        //     $pdf->SetX($box_x + 92 + 7);
        //     $pdf->Cell(26,4,'HORA:',1,0,'L');
        //     $pdf->Cell(26,4,'HORA:',1,1,'L');
        //     // $pdf->Cell(4,4,'',1,0,'L');
        //     // $pdf->Cell(4,4,'',1,1,'L');

        //     $pdf->SetX($box_x + 92 + 7);
        //     $pdf->Cell(26,4,'CODIGO:',1,0,'L');
        //     $pdf->Cell(26,4,'CODIGO:',1,1,'L');
        //     // $pdf->Cell(4,4,'',1,0,'L');
        //     // $pdf->Cell(4,4,'',1,1,'L');

        //     $pdf->SetX($box_x + 92 + 7);
        //     $pdf->Cell(52,4,'OBSERVACIONES',1,1,'C');
            
        //     $pdf->SetX($box_x + 92 + 7);
        //     $pdf->Cell(52,12,'',1,1,'C');

        //     $pdf->SetXY($box_x + 144 + 7, $box_y + 63);
        //     for ($zi=0; $zi < 6; $zi++) { 
        //         $pdf->Cell(4,4,'',1,0,'L');
        //         $pdf->Cell(4,4,'',1,0,'L');
        //         $pdf->Cell(30,4,'TEST',1,1,'L');
        //         $pdf->SetX($box_x + 144 + 7);
        //     }
        //     $pdf->Cell(4,4,'',1,0,'L');
        //     $pdf->Cell(4,4,'',1,0,'L');
        //     $pdf->Cell(30,4,'OTROS',1,1,'L');

        $box_y = 59 + $box_y + 2;
    }



    // $pdf->Cell(10, 10, 'REMITENTE', 0, 1);
    // $pdf->Rect(20, 20, 150, 50);
    $pdf->Output();
    exit;
});

Route::get('pdf3', function () {
    $pdf = new pdftest();
    $cellMargin = 2 * 1.000125;
    $lmargin = 5;
    $rmargin = 5;
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetMargins($lmargin, $rmargin);
    $pdf->Ln(0);
    $pdf->SetFont('Times', '', 6);
    $y = $pdf->GetY();
    $pdf->SetAutoPageBreak(false);

    $box_x = 5;
    $box_y = 5;
    $fila = 1;
    for ($i = 0; $i < 90; $i++) {
        if ($i  % 3 == 0 && $i !== 0) {
            $box_y = 27 + $box_y + 1;
            $box_x = 5;
            if ($fila % 10 === 0) {
                $pdf->AddPage();
                $box_y = 5;
                $box_x = 5;
            }
            $fila+=1;
        }
        // cuadro principal
        $pdf->Rect($box_x, $box_y, 65, 27);
        // codigo de barra
            $pdf->code128($box_x + 8, ($box_y + 6 + 2), '201324092503', 50, 6, false);
            $pdf->SetY($box_y);
            $pdf->SetX($box_x);
            $pdf->Cell(32,4,'MARATHON - '. ($i+1) ,0,0,'L');
            $pdf->Cell(33,4,'TELF: 980291115',0,1,'R');
            $pdf->SetX($box_x);
            $pdf->Cell(65,4,'DEL CASTILLO ROCHABRUNT MAURICIO',0,1,'L');
            $pdf->SetX($box_x);
            $pdf->Cell(65,6,'',0,1,'L');
            $pdf->SetX($box_x);
            $pdf->SetFont('Times', '', 8);
            $pdf->Cell(65,5,'20132409',0,1,'C');
            $pdf->SetX($box_x);
            $pdf->SetFont('Times', '', 6);
            $pdf->MultiCell(65,3,utf8_decode(strtolower('Avenida Santa Maria - Urbanizacion Industrial')),0,'C');
            $pdf->Ln(2);

        $box_x = 65 + $box_x + 2;
    }
    $pdf->Output();
    exit;
});

Route::get('pdf4', function () {
    $query = DB::select("select
        gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni,
        org.name, org.address as org_address, adr.district, adr.province, adr.address,
        GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created
    from
        guide gd
    join massive_load ml on ml.id_massive_load = gd.id_massive_load
    join organization as org on org.id_organization = gd.id_organization
    join address as adr on adr.id_address = gd.id_address
    join sku_product as sku on sku.id_guide = gd.id_guide
    where
        gd.id_massive_load = ?
    group by
        gd.client_barcode,
        gd.guide_number,
        gd.client_name,
        gd.client_phone1,
        gd.client_email,
        org.name,
        org.address,
        adr.district,
        adr.province,
        adr.address
    order by adr.district;", [197]);
    // dd($query);

    try {
        $pdf = new pdftest();
        $cellMargin = 2 * 1.000125;
        $lmargin = 5;
        $rmargin = 5;
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetMargins($lmargin, $rmargin);
        $pdf->Ln(0);
        $pdf->SetFont('Times', '', 7);
        $y = $pdf->GetY();
        $pdf->SetAutoPageBreak(false);

        $box_x = 5;
        $box_y = 5;

        foreach ($query as $i => $guide) {
            if ($i  % 3 == 0 && $i != 0) {
                $pdf->AddPage();
                $box_y = 5;
            }
            // cuadro principal
            $pdf->Rect($box_x, $box_y, 200, 78);

            // cuadro 1.1 REMITENTE
                //header
                $pdf->Rect($box_x + 0, $box_y + 0, 6, 37);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->TextWithDirection($box_x + 5, $box_y + 29, 'REMITENTE', 'U');

                // body
                $pdf->Rect($box_x + 6, $box_y + 0, 85, 37);
                $pdf->SetFont('Times', '', 11);
                $pdf->SetXY($box_x+6, $box_y + 1);
                $pdf->MultiCell(85,6,'NOMBRE: '. $guide->name,0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'CIUDAD: LIMA',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
                $pdf->SetX($box_x+6);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->MultiCell(85,6,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                $pdf->SetFont('Times', '', 11);
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(84,6,'DIRECCION: ' . utf8_decode(ucwords(strtolower($guide->org_address))),0,'L');

            // codigo de barra
                if (isset($guide->client_barcode)) {
                    $cod_barra = $guide->client_barcode;
                } else {
                    $cod_barra = $guide->guide_number;
                }

                $pdf->code128($box_x + 23, ($box_y + 38 + 2), $cod_barra , 50, 12, false);
                $pdf->SetXY($box_x+1, ($box_y + 52 + 2));
                $pdf->SetFont('Times', 'B', 16);
                $pdf->MultiCell(96,4,$cod_barra, 0,'C');
                $pdf->Ln(2);
            
            // cuadro 2.1 DATOS
                //header
                $pdf->Rect($box_x + 0, ($box_y + 59 + 2), 6, 17);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 76, 'DATOS', 'U');
                
                // body
                $pdf->Rect($box_x + 6, ($box_y + 59 + 2), 85, 17);
                $pdf->SetFont('Times', 'B', 12);
                $pdf->SetXY($box_x+8, ($box_y + 66 + 2));
                $pdf->MultiCell(45,4,'NRO. PIEZAS: '. 0,0,'J');
                $pdf->SetXY($box_x+8+45, ($box_y + 66 + 2));
                $pdf->MultiCell(45,4,'PESO SECO: '. 0,0,'J');
                $pdf->Line($box_x+8+41, ($box_y + 59 + 2), $box_x+8+41, ($box_y + 76 + 2));
                
                $pdf->SetX($box_x+8);
            // cuadro 1.2 DESTINATARIO
                //header
                $pdf->Rect($box_x + 93, $box_y + 0, 6, 41);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 35, 'DESTINATARIO', 'U');

                // body
                $nombre = utf8_decode(ucwords(strtolower($guide->client_name)));
                $distrito = utf8_decode(ucwords(strtolower($guide->district)));
                $direccion = utf8_decode(ucwords(strtolower($guide->address)));
                $pdf->Rect($box_x + 93 + 6, $box_y + 0, 101, 41);
                $pdf->SetFont('Times', '', 11);
                $pdf->SetXY($box_x + 92 + 7, $box_y + 1);
                $pdf->MultiCell(101,5,'NOMBRE: '. $nombre,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'DNI: '. $guide->client_dni,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'DIST.: ' . $distrito,0,'J');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'TLF.: ' . $guide->client_phone1,0,'J');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'EMAIL.: ' .utf8_decode(strtolower($guide->client_email)),0,'J');
                $pdf->SetX($box_x + 92 + 7);
                // $pdf->Cell(29,5,'TLF.: '. $guide->client_phone1 ,0,0,'L');
                // $pdf->Cell(71,5,'EMAIL: '. utf8_decode(strtolower($guide->client_email)),0,1,'L'); //lower to space
                // $pdf->SetX($box_x + 92 + 7);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->MultiCell(100,5,'DIRECCION: '. $direccion,0,'L');
                $pdf->SetFont('Times', '', 11);

            // cuadro 2.2 CONTENIDO
                //header
                $pdf->Rect($box_x + 93, $box_y + 42, 6, 36);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 70, 'CONTENIDO', 'U');

                // body
                $pdf->Rect($box_x + 93 + 6, $box_y + 42, 101, 36);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x + 93 + 6, $box_y + 44);

                $contenidoArray = explode(",", $guide->contenido);
                foreach ($contenidoArray as $key => $product) {
                    $pdf->MultiCell(101,3,utf8_decode(ucwords(strtolower($product))),0,'L');
                    $pdf->SetX($box_x + 93 + 6);
                }
            $box_y = 78+ $box_y + 4;
        }
        $pdf->Output();
        exit;
        // $disk = Storage::disk('cargo');
        // $fileName = date('YmdHis') . '_cc_' . '51616516' . '_' . rand(1, 100) . '.pdf';
        // $save = $disk->put($fileName, $pdf->Output('S', '', true));
        // if (!$save) {
        //     throw new Exception('No se pudo grabar la hoja de ruta');
        // }
        // $res['file_name'] = $fileName;
    } catch (Exception $e) {
        dd($e);
    }

    return 'hola';
});

Route::get('pdf5', function () {
    $query = DB::select("select
        gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni,
        org.name, org.address as org_address, adr.district, adr.province, adr.address,
        GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created
    from
        guide gd
    join massive_load ml on ml.id_massive_load = gd.id_massive_load
    join organization as org on org.id_organization = gd.id_organization
    join address as adr on adr.id_address = gd.id_address
    join sku_product as sku on sku.id_guide = gd.id_guide
    where
        gd.id_massive_load = ?
    group by
        gd.client_barcode,
        gd.guide_number,
        gd.client_name,
        gd.client_phone1,
        gd.client_email,
        org.name,
        org.address,
        adr.district,
        adr.province,
        adr.address
    order by adr.district;", [252]);

    $motivos = DB::table('motive')->where('status', 'ACTIVO')->where('estado', 'No entregado')->where('starred',1)->get();
    // dd($motivos);

    try {
        $pdf = new pdftest();
        $cellMargin = 2 * 1.000125;
        $lmargin = 5;
        $rmargin = 5;
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetMargins($lmargin, $rmargin);
        $pdf->Ln(0);
        $pdf->SetFont('Times', '', 7);
        $y = $pdf->GetY();
        $pdf->SetAutoPageBreak(false);

        $box_x = 5;
        $box_y = 5;

        foreach ($query as $i => $guide) {
            if ($i  % 2 == 0 && $i != 0) {
                $pdf->AddPage();
                $box_y = 5;
            }
            // cuadro principal
            $pdf->Rect($box_x, $box_y, 200, 110);

            // cuadro 1.1 REMITENTE
                //header
                $pdf->Rect($box_x + 0, $box_y + 0, 6, 32);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 27, 'REMITENTE', 'U');

                // body
                $pdf->Rect($box_x + 6, $box_y + 0, 85, 32);
                $pdf->SetFont('Times', '', 10);
                $pdf->SetXY($box_x+8, $box_y + 1);
                $pdf->MultiCell(89,5,'NOMBRE: '. $guide->name,0,'J');
                $pdf->SetX($box_x+8);
                $pdf->MultiCell(89,5,'CIUDAD: LIMA',0,'J');
                $pdf->SetX($box_x+8);
                $pdf->MultiCell(89,5,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
                $pdf->SetX($box_x+8);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->MultiCell(89,5,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                $pdf->SetFont('Times', '', 10);
                $pdf->SetX($box_x+8);
                $pdf->MultiCell(84,5,'DIRECCION: ' . utf8_decode(ucwords(strtolower($guide->org_address))),0,'L');

            // codigo de barra
                if (isset($guide->client_barcode)) {
                    $cod_barra = $guide->client_barcode;
                } else {
                    $cod_barra = $guide->guide_number;
                }

                $pdf->code128($box_x + 23, ($box_y + 32 + 2), $cod_barra , 50, 12, false);
                $pdf->SetXY($box_x+1, ($box_y + 44 + 2));
                $pdf->SetFont('Times', 'B', 10);
                $pdf->MultiCell(96,4,$cod_barra, 0,'C');
                $pdf->Ln(2);
            
            // cuadro 2.1 DATOS
                //header
                $pdf->Rect($box_x + 0, ($box_y + 50 + 2), 6, 17);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 66, 'DATOS', 'U');
                
                // body
                $pdf->Rect($box_x + 6, ($box_y + 50 + 2), 85, 17);
                $pdf->SetFont('Times', 'B', 12);
                $pdf->SetXY($box_x+8, ($box_y + 57 + 2));
                $pdf->MultiCell(45,4,'NRO. PIEZAS: '. 0,0,'J');
                $pdf->SetXY($box_x+8+45, ($box_y + 57 + 2));
                $pdf->MultiCell(45,4,'PESO SECO: '. 0,0,'J');
                $pdf->Line($box_x+8+41, ($box_y + 50 + 2), $box_x+8+41, ($box_y + 67 + 2));
                
                $pdf->SetX($box_x+8);

            // cuadro 3.1 DATOS DE ENTREGA
                //header
                $pdf->Rect($box_x + 0, ($box_y + 68 + 2) , 6, 40);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 108, 'DATOS DE ENTREGA', 'U');

                // body
                $pdf->Rect($box_x + 6, ($box_y + 68 + 2), 85, 40);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x+6, ($box_y + 72 + 2));
                $pdf->MultiCell(85,6,'FIRMA:  ____________________________________________',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'NOMBRE:  __________________________________________',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'VINCULO:  _________________________________________',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'DNI:  _______________________________________________',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'FECHA: ________ / ________ / ________',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'HORA: ______________:______________',0,'J');
                $pdf->SetX($box_x+6);

            // cuadro 1.2 DESTINATARIO
                //header
                $pdf->Rect($box_x + 93, $box_y + 0, 6, 32);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 30, 'DESTINATARIO', 'U');

                // body
                $nombre = utf8_decode(ucwords(strtolower($guide->client_name)));
                $distrito = utf8_decode(ucwords(strtolower($guide->district)));
                $direccion = utf8_decode(ucwords(strtolower($guide->address)));
                $pdf->Rect($box_x + 93 + 6, $box_y + 0, 101, 32);
                $pdf->SetFont('Times', '', 10);
                $pdf->SetXY($box_x + 92 + 7, $box_y + 1);
                $pdf->MultiCell(100,4,'NOMBRE: '. $nombre,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(100,4,'DNI: '. $guide->client_dni,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(100,4,'DIST.: ' . $distrito,0,'J');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->Cell(29,4,'TLF.: '. $guide->client_phone1 ,0,0,'L');
                $pdf->Cell(71,4,'EMAIL: '. utf8_decode(strtolower($guide->client_email)),0,1,'L'); //lower to space
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(100,4,'DIRECCION: '. $direccion,0,'L');

            // cuadro 2.2 CONTENIDO
                //header
                $pdf->Rect($box_x + 93, $box_y + 33, 6, 36);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 64, 'CONTENIDO', 'U');

                // body
                $pdf->Rect($box_x + 93 + 6, $box_y + 33, 101, 36);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x + 93 + 6, $box_y + 34);

                $contenidoArray = explode(",", $guide->contenido);
                foreach ($contenidoArray as $key => $product) {
                    // $pdf->MultiCell(89,2,$product->sku_code.' - '.$product->sku_description,0,'J');
                    $pdf->MultiCell(101,3,utf8_decode(ucwords(strtolower($product))),0,'L');
                    $pdf->SetX($box_x + 93 + 6);
                }

            // cuadro 2.3 OBSERVACIONES
                //header
                // $pdf->Rect($box_x + 99, $box_y + 66, 6, 25);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x + 86 + 7, $box_y + 70);
                $pdf->Cell(28,4,'PRIMERA VISITA',1,0,'L');
                $pdf->Cell(28,4,'SEGUNDA VISITA',1,0,'L');
                $pdf->Cell(4,4,'1',1,0,'L');
                $pdf->Cell(4,4,'2',1,0,'L');
                $pdf->Cell(43,4,'MOTIVO DE REZAGO',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'FECHA:',1,0,'L');
                $pdf->Cell(28,5,'FECHA:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'HORA:',1,0,'L');
                $pdf->Cell(28,5,'HORA:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'CODIGO:',1,0,'L');
                $pdf->Cell(28,5,'CODIGO:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(56,5,'OBSERVACIONES',1,1,'C');
                
                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(56,16,'',1,1,'C');

                $pdf->SetXY($box_x + 142 + 7, $box_y + 74);
                foreach ($motivos as $key => $motivo) {
                    $pdf->Cell(4,5,'',1,0,'L');
                    $pdf->Cell(4,5,'',1,0,'L');
                    $pdf->Cell(43,5,utf8_decode($motivo->name),1,1,'L');
                    $pdf->SetX($box_x + 142 + 7);
                }
                $pdf->Cell(4,6,'',1,0,'L');
                $pdf->Cell(4,6,'',1,0,'L');
                $pdf->Cell(43,6,'OTROS',1,1,'L');

            $box_y = 110 + $box_y + 5;
        }
        $pdf->Output();
        exit;
        // $disk = Storage::disk('cargo');
        // $fileName = date('YmdHis') . '_cc_' . '51616516' . '_' . rand(1, 100) . '.pdf';
        // $save = $disk->put($fileName, $pdf->Output('S', '', true));
        // if (!$save) {
        //     throw new Exception('No se pudo grabar la hoja de ruta');
        // }
        // $res['file_name'] = $fileName;
    } catch (Exception $e) {
        dd($e);
    }

    return 'hola';
});

Route::get('pdf6', function () {
    $query = DB::select("select
        gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni,
        org.name, org.address as org_address, adr.district, adr.province, adr.address,
        GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created
    from
        guide gd
    join massive_load ml on ml.id_massive_load = gd.id_massive_load
    join organization as org on org.id_organization = gd.id_organization
    join address as adr on adr.id_address = gd.id_address
    join sku_product as sku on sku.id_guide = gd.id_guide
    where
        gd.id_massive_load = ?
    group by
        gd.client_barcode,
        gd.guide_number,
        gd.client_name,
        gd.client_phone1,
        gd.client_email,
        org.name,
        org.address,
        adr.district,
        adr.province,
        adr.address
    order by adr.district;", [153]);
    $motivos = DB::table('motive')->where('status', 'ACTIVO')->where('estado', 'No entregado')->where('starred',1)->get();
    // dd($query);

    try {
        $pdf = new pdftest();
        $cellMargin = 2 * 1.000125;
        $lmargin = 5;
        $rmargin = 5;
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetMargins($lmargin, $rmargin);
        $pdf->Ln(0);
        $pdf->SetFont('Times', '', 7);
        $y = $pdf->GetY();
        $pdf->SetAutoPageBreak(false);

        $box_x = 5;
        $box_y = 5;

        foreach ($query as $i => $guide) {
            if ($i  % 2 == 0 && $i != 0) {
                $pdf->AddPage();
                $box_y = 5;
            }
            // cuadro principal
            $pdf->Rect($box_x, $box_y, 200, 119);

            // cuadro 1.1 REMITENTE
                //header
                $pdf->Rect($box_x + 0, $box_y + 0, 6, 37);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->TextWithDirection($box_x + 5, $box_y + 29, 'REMITENTE', 'U');

                // body
                $pdf->Rect($box_x + 6, $box_y + 0, 85, 37);
                $pdf->SetFont('Times', '', 11);
                $pdf->SetXY($box_x+6, $box_y + 1);
                $pdf->MultiCell(85,6,'NOMBRE: '. $guide->name,0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'CIUDAD: LIMA',0,'J');
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(85,6,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
                $pdf->SetX($box_x+6);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->MultiCell(85,6,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                $pdf->SetFont('Times', '', 11);
                $pdf->SetX($box_x+6);
                $pdf->MultiCell(84,6,'DIRECCION: ' . utf8_decode(ucwords(strtolower($guide->org_address))),0,'L');

            // codigo de barra
                if (isset($guide->client_barcode)) {
                    $cod_barra = $guide->client_barcode;
                } else {
                    $cod_barra = $guide->guide_number;
                }

                $pdf->code128($box_x + 23, ($box_y + 38 + 2), $cod_barra , 50, 12, false);
                $pdf->SetXY($box_x+1, ($box_y + 52 + 2));
                $pdf->SetFont('Times', 'B', 16);
                $pdf->MultiCell(96,4,$cod_barra, 0,'C');
                $pdf->Ln(2);
            
            // cuadro 2.1 DATOS
                //header
                $pdf->Rect($box_x + 0, ($box_y + 59 + 2), 6, 17);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 76, 'DATOS', 'U');
                
                // body
                $pdf->Rect($box_x + 6, ($box_y + 59 + 2), 85, 17);
                $pdf->SetFont('Times', 'B', 12);
                $pdf->SetXY($box_x+8, ($box_y + 66 + 2));
                $pdf->MultiCell(45,4,'NRO. PIEZAS: '. 0,0,'J');
                $pdf->SetXY($box_x+8+45, ($box_y + 66 + 2));
                $pdf->MultiCell(45,4,'PESO SECO: '. 0,0,'J');
                $pdf->Line($box_x+8+41, ($box_y + 59 + 2), $box_x+8+41, ($box_y + 76 + 2));
                
                $pdf->SetX($box_x+8);
            // cuadro 1.2 DESTINATARIO
                //header
                $pdf->Rect($box_x + 93, $box_y + 0, 6, 41);
                $pdf->SetFont('Times', 'B', 11);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 35, 'DESTINATARIO', 'U');

                // body
                $nombre = utf8_decode(ucwords(strtolower($guide->client_name)));
                $distrito = utf8_decode(ucwords(strtolower($guide->district)));
                $direccion = utf8_decode(ucwords(strtolower($guide->address)));
                $pdf->Rect($box_x + 93 + 6, $box_y + 0, 101, 41);
                $pdf->SetFont('Times', '', 11);
                $pdf->SetXY($box_x + 92 + 7, $box_y + 1);
                $pdf->MultiCell(101,5,'NOMBRE: '. $nombre,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'DNI: '. $guide->client_dni,0,'L');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'DIST.: ' . $distrito,0,'J');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'TLF.: ' . $guide->client_phone1,0,'J');
                $pdf->SetX($box_x + 92 + 7);
                $pdf->MultiCell(101,5,'EMAIL.: ' .utf8_decode(strtolower($guide->client_email)),0,'J');
                $pdf->SetX($box_x + 92 + 7);
                // $pdf->Cell(29,5,'TLF.: '. $guide->client_phone1 ,0,0,'L');
                // $pdf->Cell(71,5,'EMAIL: '. utf8_decode(strtolower($guide->client_email)),0,1,'L'); //lower to space
                // $pdf->SetX($box_x + 92 + 7);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->MultiCell(100,5,'DIRECCION: '. $direccion,0,'L');
                $pdf->SetFont('Times', '', 11);

            // cuadro 3.1 DATOS DE ENTREGA
                //header
                $pdf->Rect($box_x + 0, ($box_y + 77 + 2) , 6, 40);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 5, $box_y + 118, 'DATOS DE ENTREGA', 'U');

                // body
                $pdf->Rect($box_x + 6, ($box_y + 77 + 2), 85, 40);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x+7, ($box_y + 80 + 2));
                $pdf->MultiCell(85,6,'FIRMA:  ____________________________________________',0,'J');
                $pdf->SetX($box_x+7);
                $pdf->MultiCell(85,6,'NOMBRE:  __________________________________________',0,'J');
                $pdf->SetX($box_x+7);
                $pdf->MultiCell(85,6,'VINCULO:  _________________________________________',0,'J');
                $pdf->SetX($box_x+7);
                $pdf->MultiCell(85,6,'DNI:  ______________________________________________',0,'J');
                $pdf->SetX($box_x+7);
                $pdf->MultiCell(85,6,'FECHA: ________ / ________ / ________',0,'J');
                $pdf->SetX($box_x+7);
                $pdf->MultiCell(85,6,'HORA: ______________:______________',0,'J');
                $pdf->SetX($box_x+7);

            // cuadro 2.2 CONTENIDO
                //header
                $pdf->Rect($box_x + 93, $box_y + 42, 6, 36);
                $pdf->SetFont('Times', 'B', 10);
                $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 70, 'CONTENIDO', 'U');

                // body
                $pdf->Rect($box_x + 93 + 6, $box_y + 42, 101, 36);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x + 93 + 6, $box_y + 44);

                $contenidoArray = explode(",", $guide->contenido);
                foreach ($contenidoArray as $key => $product) {
                    $pdf->MultiCell(101,3,utf8_decode(ucwords(strtolower($product))),0,'L');
                    $pdf->SetX($box_x + 93 + 6);
                }

            // cuadro 2.3 OBSERVACIONES
                //header
                // $pdf->Rect($box_x + 99, $box_y + 66, 6, 25);
                $pdf->SetFont('Times', '', 9);
                $pdf->SetXY($box_x + 86 + 7, $box_y + 79);
                $pdf->Cell(28,4,'PRIMERA VISITA',1,0,'L');
                $pdf->Cell(28,4,'SEGUNDA VISITA',1,0,'L');
                $pdf->Cell(4,4,'1',1,0,'L');
                $pdf->Cell(4,4,'2',1,0,'L');
                $pdf->Cell(43,4,'MOTIVO DE REZAGO',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'FECHA:',1,0,'L');
                $pdf->Cell(28,5,'FECHA:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'HORA:',1,0,'L');
                $pdf->Cell(28,5,'HORA:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(28,5,'CODIGO:',1,0,'L');
                $pdf->Cell(28,5,'CODIGO:',1,1,'L');
                // $pdf->Cell(4,4,'',1,0,'L');
                // $pdf->Cell(4,4,'',1,1,'L');

                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(56,5,'OBSERVACIONES',1,1,'C');
                
                $pdf->SetX($box_x + 86 + 7);
                $pdf->Cell(56,16,'',1,1,'C');

                $pdf->SetXY($box_x + 142 + 7, $box_y + 83);
                foreach ($motivos as $key => $motivo) {
                    $pdf->Cell(4,5,'',1,0,'L');
                    $pdf->Cell(4,5,'',1,0,'L');
                    $pdf->Cell(43,5,utf8_decode($motivo->name),1,1,'L');
                    $pdf->SetX($box_x + 142 + 7);
                }
                $pdf->Cell(4,6,'',1,0,'L');
                $pdf->Cell(4,6,'',1,0,'L');
                $pdf->Cell(43,6,'OTROS',1,1,'L');


            $box_y = 119+ $box_y + 3;
        }
        $pdf->Output();
        exit;
        // $disk = Storage::disk('cargo');
        // $fileName = date('YmdHis') . '_cc_' . '51616516' . '_' . rand(1, 100) . '.pdf';
        // $save = $disk->put($fileName, $pdf->Output('S', '', true));
        // if (!$save) {
        //     throw new Exception('No se pudo grabar la hoja de ruta');
        // }
        // $res['file_name'] = $fileName;
    } catch (Exception $e) {
        dd($e);
    }

    return 'hola';
});
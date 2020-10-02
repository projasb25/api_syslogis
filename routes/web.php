<?php

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

    $pdf->SetDrawColor(69, 69, 69);
    $pdf->MultiCell(198, 6, utf8_decode('Transportista:'), 1, 'L');
    $y = $pdf->GetY();
    $pdf->MultiCell(198, 6, utf8_decode('Nombres y Apellidos:'), 1, 'L');
    $y = $pdf->GetY();
    $pdf->MultiCell(198, 6, utf8_decode('DNI:'), 1, 'L');
    $y = $pdf->GetY();

    $pdf->SetDrawColor(150, 153, 141);
    $pdf->Line(10, $y + 2, 195, $y + 2);
    $pdf->Ln(4);
    $y = $pdf->GetY();

    $pdf->SetDrawColor(69, 69, 69);
    $pdf->MultiCell(120, 40, utf8_decode(''), 1, 'L');
    $pdf->SetXY($lmargin + 120, $y);
    $pdf->MultiCell(78, 40, '', 1, 'L');
    $y = $pdf->GetY();

    $pdf->Text(22, $y - 10, '____________________________________________________________');
    $pdf->Text(55, $y - 5, 'FIRMA');

    $pdf->Text(153, $y - 5, 'HUELLA DACTILAR');

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
    $pdf->SetFont('Times', '', 8);
    $y = $pdf->GetY();
    // var_dump($pdf->_getpagesize('a4')); (210.00155555556) - (297.00008333333) 

    // 297 - 10 (margin xy) 
    // 280 / 3 = 93
    // 90


    $box_x = 5;
    $box_y = 5;
    for ($i = 0; $i < 5; $i++) {
        if ($i  % 2 == 0 && $i != 0) {
            $pdf->AddPage();
            $box_y = 5;
        }
        // cuadro principal
        $pdf->Rect($box_x, $box_y, 190, 90);

        // // cuadro 1.1
        //     //header
        //     $pdf->Rect($box_x + 1, $box_y + 1, 6, 40);
        //     $pdf->SetFont('Times', 'B', 8);
        //     $pdf->TextWithDirection($box_x + 5, $box_y + 24, 'REMITENTE', 'U');

        //     // body
        //     $pdf->Rect($box_x + 7, $box_y + 1, 80, 40);

        // // cuadro 2.1
        // $pdf->Rect($box_x + 1, ($box_y + 55 + 2), 6, 13);
        // $pdf->SetFont('Times', 'B', 8);
        // // $pdf->MultiCell(100,5,'asdfasdf',1,'J');
        // $pdf->TextWithDirection($box_x + 5, $box_y + 68, 'DATOS', 'U');

        // // cuadro 3.1
        // $pdf->Rect($box_x + 1, ($box_y + 70 + 2) , 6, 47);
        // $pdf->SetFont('Times', 'B', 8);
        // $pdf->TextWithDirection($box_x + 5, $box_y + 110, 'DATOS DE ENTREGA', 'U');

        $box_y = 90 + $box_y + 2;
    }



    // $pdf->Cell(10, 10, 'REMITENTE', 0, 1);
    // $pdf->Rect(20, 20, 150, 50);
    $pdf->Output();
    exit;
});

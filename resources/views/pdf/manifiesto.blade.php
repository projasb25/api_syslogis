<!DOCTYPE html>
<html>

<head>
    <title>Hi</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-size: 12px;
            font-family: 'Times New Roman', Times, serif;
        }


        .mid-td {
            border-top: 1px white solid;
            border-bottom: 1px white solid;
        }

        .table-body td {
            vertical-align: top;
        }

        .hr {
            width: 100%;
            border: 1px solid #bababa;
            margin: 10px 0;
        }

        .table-content {
            border-collapse: collapse;
            border-spacing: 0;
            /*table-layout: fixed;*/
        }

        .table-content th,
        .table-content td {
            border: black 1px solid;
            padding: .2em 0.3em;
        }
    </style>
</head>

<body>
    <div style="text-align: center; font-size: 12px">
        <table style="margin-left: auto; margin-right: auto;" cellpadding="3">
            <tr>
                <td style="width: 80px; text-align: left">Empresa:</td>
                <td style="width: 200px;">{{strtoupper(utf8_decode($rows[0]->provider_name))}}</td>
                <td style="width: 10px;"></td>
                <td style="width: 100px; text-align: left">Fecha Asignación:</td>
                <td style="width: 120px;">{{$fecha_asignacion}}</td>
                <td style="width: 10px;"></td>
                <td style="width: 110px; text-align: left">Nro de celular: </td>
                <td style="width: 110px;">________________________</td>
                <td rowspan=4>
                    @php
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        echo 
                            '<img
                                style="padding-left: 10px;width: 180px;"
                                src="data:image/png;base64,' . base64_encode($generator->getBarcode(str_pad($rows[0]->id_shipping_order, 7, '0', STR_PAD_LEFT), $generator::TYPE_CODE_128, 2, 70)) . '"
                            />';
                    @endphp
                    
                </td>
            </tr>
            <tr>
                <td style="text-align: left">Conductor: </td>
                <td>{{strtoupper(utf8_decode($rows[0]->first_name .' '. $rows[0]->last_name))}}</td>
                <td></td>
                <td style="text-align: left">Placa:</td>
                <td>{{strtoupper(utf8_decode($rows[0]->plate_number))}}</td>
                <td></td>
                <td style="text-align: left">Bateria del celular: </td>
                <td>________________________</td>
            </tr>
            <tr>
                <td style="text-align: left">Cod. Envio: </td>
                <td>{{$rows[0]->id_shipping_order}}</td>
                <td></td>
                <td style="text-align: left">Hora de Asignacion: </td>
                <td> {{$hora_asignacion}} </td>
                <td></td>
                <td style="text-align: left">Total guias: {{$total_guias}}</td>
                <td style="text-align: center">Total Bultos: {{$total_bultos}}</td>
            </tr>
            </tr>
            <tr>
                <td style="text-align: left">Hora de salida: </td>
                <td>__________ : __________</td>
                <td></td>
                <td style="text-align: left">Hora de llegada: </td>
                <td>_________ : _________</td>
                <td></td>
                <td style="text-align: left">Total direcciones: {{$total_direcciones}}</td>
                <td>&nbsp;</td>
            </tr>
        </table>
        <div class="hr"></div>
        <table style="width: 100%; border-color: black; border-collapse: collapse;" border="1" cellpadding="3" class="table-body">
            <tr>
                <td style="width: 22%; height: 50px" colspan="2">Nombres del Axuliar:</td>
                <td style="width: 13%; height: 50px">DNI:</td>
                <td style="width: 13%; height: 50px">Firma:</td>
                <td style="width: 4%; height: 50px;" class="mid-td">&nbsp;</td>
                <td style="width: 12%; height: 50px">Entregas:</td>
                <td style="width: 12%; height: 50px">Rezagos:</td>
                <td style="width: 12%; height: 50px">Ausentes:</td>
                <td style="width: 12%; height: 50px">H. Retorno:</td>
            </tr>
            <tr>
                <td style="width: 22%; height: 50px" colspan="2">Lider que manifesto:</td>
                <td style="width: 13%; height: 50px">Firma Lider:</td>
                <td style="width: 13%; height: 50px">Apoyo:</td>
                <td style="width: 4%; height: 50px;" class="mid-td">&nbsp;</td>
                <td style="width: 22%; height: 50px" colspan="2">Lider que descargo:</td>
                <td style="width: 13%; height: 50px">Firma:</td>
                <td style="width: 13%; height: 50px">Fotos (SI/NO):</td>
            </tr>
        </table>
        <div class="hr"></div>
        <table style="width: 100%;" class="table-content">
            <thead>
                <tr>
                    <th style="width: 1%;">Nro</th>
                    <th style="width: 12%;">Cod. Barra</th>
                    <th style="width: 12%;">Cod. Seguimiento</th>
                    <th style="width: 10%;">Cod. Alt</th>
                    <th style="width: 20%;">Distrito</th>
                    <th>Direccion de Entrea</th>
                    <th style="width: 1%;">Bults</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $key => $item)
                <tr>
                    <td style="text-align: center">{{$key+1}}</td>
                    <td>{{$item->client_barcode}}</td>
                    <td>{{$item->seg_code}}</td>
                    <td>{{$item->alt_code1}}</td>
                    <td>{{$item->district}}</td>
                    <td>{{ucwords(strtolower(utf8_decode(utf8_encode($item->address))))}}</td>
                    <td style="text-align: center">{{$item->nro_guias}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
</body>
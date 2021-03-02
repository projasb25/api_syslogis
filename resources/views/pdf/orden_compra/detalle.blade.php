<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de compra</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
</head>
<style>
    .body{
        font-size: 13px;
    }
    .cabecera {
        width: 100%;
    }
    table.cabecera th.from, th.to {
        width: 50%;
        font-size:20px;
    }
    .cabecera thead th {
        padding-bottom: 20px;
    }
    .cabecera td {
        text-align: justify;
        padding-bottom: 5px;
        padding-right: 10px;
    }

    table.detalle {
        width: 100%;
    }
    table.detalle th {
        border: 1px solid black;
        text-align: center;
    }
    table.detalle th.nro, th.cantidad {
        width: 15%;
    }
    table.detalle th.origen, th.ubicacion {
        width: 20%;
    }
    table.detalle th.cod_producto{
        width: 30%;
    }
    table.detalle tbody td {
        text-align: center;
    }
</style>
<body>
    <div class="container">
        <h4 class="text-center">Orden Compra Nº {{$id_purchase_order}}</h4>
        <br>
        <table class="cabecera">
            <thead>
              <tr>
                <th class="from">DETALLE DE LA ORDEN DE COMPRA</th>
                <th class="to"></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="text-align: justify"><b>Razon Social:</b> {{$purchase_order[0]->company_name}}</td>
                <td><b>Transportista:</b> {{$purchase_order[0]->name}}</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Tipo Documento:</b> {{$purchase_order[0]->doc_type}}</td>
                <td><b>Vehiculo:</b> {{$purchase_order[0]->model . ' - ' .$purchase_order[0]->plate_number}}</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Nro Documento:</b> {{$purchase_order[0]->doc_number}}</td>
                <td><b>Licencia de Conductor:</b> {{$purchase_order[0]->driver_license}}</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Fecha de Emisión:</b> {{$purchase_order[0]->date_updated}}</td>
                <td></td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Nr O/Compra:</b> {{$purchase_order[0]->purchase_order_number}}</td>
                <td></td>
              </tr>
            </tbody>
          </table>
          <br>
          <table class="detalle">
                <thead>
                    <tr>
                        <th class="nro">No.</th>
                        <th class="cod_prod">Cod. Producto</th>
                        <th class="cantidad">Cant</th>
                        <th class="origen">Descontado</th>
                        <th class="ubicacion">Ubicación (P - N - C)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase_order as $key => $item)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td style="text-align:left;padding-left: 10px;">{{$item->product_code}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>Disponible</td>
                        <td>{{$item->hallway . ' - ' . $item->level . ' - ' . $item->column}}</td>
                    </tr>
                    @endforeach
                </tbody>
          </table>
    </div>

    </div>
    {{-- <table>
        <thead><tr>
            <th>No.</th>
            <th>Product Code</th>
            <th>Description</th>
            <th>Ubicacion</th>
        </tr></thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>asdf</td>
                <td>asdf</td>
                <td>1-1-1</td>
            </tr>
        </tbody>
    </table> --}}
</body>
</html>
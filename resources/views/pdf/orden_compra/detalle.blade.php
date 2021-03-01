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
        <h4 class="text-center">Orden Compra Nº 20</h4>
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
                <td style="text-align: justify"><b>Razon Social:</b> COMPAÑIA MINERA CHUNGAR S.A.C.</td>
                <td><b>Transportista:</b> SUPERCARGO</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Tipo Documento:</b> RUC</td>
                <td><b>Vehiculo:</b> HYUNDAI - 81D-SDF</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Nro Documento:</b> 201324092503</td>
                <td><b>Licencia de Conductor:</b> Q16165S9836</td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Fecha de Emisión:</b> 01/03/2021</td>
                <td></td>
              </tr>
              <tr>
                <td style="text-align: justify"><b>Nr O/Compra:</b> 516156165</td>
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
                        <th class="ubicacion">Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < 20; $i++)
                    <tr>
                        <td>{{$i+1}}</td>
                        <td style="text-align:left;padding-left: 10px;">Lorem.</td>
                        <td>{{$i*2}}</td>
                        <td>Disponible</td>
                        <td>1-1-1</td>
                    </tr>
                    @endfor
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
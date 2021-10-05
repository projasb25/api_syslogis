@php
    $fechas = array_unique(array_column($cuadro_detalle, 'fecha_entrega'));
    sort($fechas);
@endphp
<table>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td colspan="6">RESULTADOS QAYARIX HOME DELIVERY</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">Etiquetas de fila</th>
            @foreach ($fechas as $item)
                <th style="color:white;background-color:#bf000b;border: 1px solid black;">{{$item}}</th>
            @endforeach
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">Total General</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cuadro_general as $data)
        <tr>
            <td style="color:black;background-color:#F2DCDB;font-weight: bold;"><span>{{$data->fecha}}</span></td>
            @foreach ($fechas as $item)
                <td style="color:black;background-color:#F2DCDB;font-weight: bold;">
                    {{ (new \App\Helpers\ArrayHelper)->search_by_two_keys($cuadro_detalle, 'fecha_promesa', 'fecha_entrega', $data->fecha, $item, 'total_suma') }}
                </td>
            @endforeach
            <td style="color:black;background-color:#F2DCDB;font-weight: bold;">{{$data->total_suma}}</td>
            <td style='text-align: right;'>{{number_format($data->eficiencia,2)}}%</td>
        </tr>
        @if ($data->total_entregado > 0)
            <tr>
                <td style="margin-left:5px"><span style="white-space:pre;">&nbsp;&nbsp;&nbsp;&nbsp;{{($type=='RECOLECCION')?'RECOLECCION COMPLETA':'ENTREGADO'}}</span></td>
                @foreach ($fechas as $item)
                    <td>
                        {{ (new \App\Helpers\ArrayHelper)->search_by_two_keys($cuadro_detalle, 'fecha_promesa', 'fecha_entrega', $data->fecha, $item, 'total_entregado') }}
                    </td>
                @endforeach
                <td>{{$data->total_entregado}}</td>
            </tr>
        @endif
        @if ($data->total_no_entregado > 0)
            <tr>
                <td style="margin-left:5px"><span style="white-space:pre;">&nbsp;&nbsp;&nbsp;&nbsp;{{($type=='RECOLECCION')?'NO RECOLECTADO':'NO ENTREGADO'}}</span></td>
                @foreach ($fechas as $item)
                    <td>
                        {{ (new \App\Helpers\ArrayHelper)->search_by_two_keys($cuadro_detalle, 'fecha_promesa', 'fecha_entrega', $data->fecha, $item, 'total_no_entregado') ?: '' }}
                    </td>
                @endforeach
                <td>{{$data->total_no_entregado}}</td>
            </tr>
        @endif
        @endforeach
        <tr>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;font-weight: bold;">Total general</td>
            @foreach ($fechas as $item)
                <td style="color:white;background-color:#bf000b;border: 1px solid black;font-weight: bold;">
                    {{(new \App\Helpers\ArrayHelper)->sum_total_by_key($cuadro_detalle, 'fecha_entrega', $item, 'total_suma')}}
                </td>
            @endforeach
            <td style="color:white;background-color:#bf000b;border: 1px solid black;font-weight: bold;">{{array_sum(array_column($cuadro_general, 'total_suma'))}}</td>
        </tr>
    </tbody>
</table>
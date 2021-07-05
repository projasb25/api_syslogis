@php
    $fechas = array_unique(array_column($cuadro_detalle, 'fecha_entrega'));
    sort($fechas);

    function test() {
        return 'hola';
    }
@endphp
<table>
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
            <td>{{$data->fecha}}</td>
            @foreach ($fechas as $item)
                <td>
                    {{ (new \App\Helpers\ArrayHelper)->search_by_two_keys($cuadro_detalle, 'fecha_promesa', 'fecha_entrega', $data->fecha, $item) }}
                </td>
            @endforeach
            <td>{{$data->total_suma}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@php
    $fechas = array_unique(array_column($cuadro_detalle, 'fecha_entrega'));
    sort($fechas);
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
                    @php
                        foreach ($cuadro_detalle as $key => $val) {
                            if ($val->fecha_promesa === $data->fecha && $val->fecha_entrega === $item) {
                                echo $val['total_suma'];
                            } else {
                                echo 'nada';
                            }
                        }
                    @endphp
                </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
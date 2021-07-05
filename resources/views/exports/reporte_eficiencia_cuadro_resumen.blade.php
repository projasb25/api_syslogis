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
                @php
                    echo test();
                @endphp
                {{-- @foreach ($cuadro_detalle as $val)
                    <td>
                        @if ($val->fecha_promesa === $data->fecha && $val->fecha_entrega === $item)
                            {{$val->total_suma}}
                        @else
                            0
                        @endif
                    </td>
                @endforeach --}}
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
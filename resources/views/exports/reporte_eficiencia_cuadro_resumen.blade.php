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

    </tbody>
</table>
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
    <tr>
        <td colspan="5" style="color:white;background-color:#bf000b;border: 1px solid black;">Count of ESTADO GN7 QAYARIX</td>
    </tr>
    <thead>
        <tr>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">FECHA RECEPCION DATA</th>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">ENTREGADO</th>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">NO ENTREGADO</th>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">Suma total</th>
            <th style="color:white;background-color:#bf000b;border: 1px solid black;">Efectividad</th>
        </tr>
        </thead>
        <tbody>
        @php
            $total_entregado = 0; $total_no_entregado = 0; $total_suma = 0; $grand_total=0;
        @endphp
        @foreach($detalle as $item)
            @php $item_eficiencia = null; @endphp
            <tr>
                <td>{{$item->fecha}}</td>
                <td>{{$item->total_entregado}}</td>
                <td>{{$item->total_no_entregado}}</td>
                <td>{{$item->total_suma}}</td>
                <td style='text-align: right;'>{{number_format($item->efectividad,2)}}%</td>
            </tr>
            @php
                $total_entregado += $item->total_entregado; 
                $total_no_entregado += $item->total_no_entregado; 
                $total_suma += $item->total_suma;
            @endphp
        @endforeach
        <tr>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;">Suma Total</td>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;">{{$total_entregado}}</td>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;">{{$total_no_entregado}}</td>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;">{{$total_suma}}</td>
            <td style="color:white;background-color:#bf000b;border: 1px solid black;"></td>
        </tr>
        
    </tbody>
    <tr>
        <td colspan="26"></td>
    </tr>
    <tr>
        <td colspan="2">Detalle de Rezagos</td>
        <td colspan="24"></td>
    </tr>
    <tr>
        <td colspan="26"></td>
    </tr>
    <tr>
        <td style="color:white;background-color:#bf000b;border: 1px solid black;">Row Labels</td>
        <td style="color:white;background-color:#bf000b;border: 1px solid black;">Grand Total</td>
        <td colspan="24"></td>
    </tr>
    @foreach ($motivos as $item)
        <tr>
            <td>{{$item->motive}}</td>
            <td>{{$item->conteo}}</td>
        </tr>
        @php
            $grand_total += $item->conteo; 
        @endphp
    @endforeach
    <tr>
        <td style="color:white;background-color:#bf000b;border: 1px solid black;">Grand Total</td>
        <td style="color:white;background-color:#bf000b;border: 1px solid black;">{{$grand_total}}</td>
        <td colspan="24"></td>
    </tr>
</table>
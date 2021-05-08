<table style="border: 1px solid black;">
    <thead>
        <tr>
            <th style="background: #bf000b;color:white;text-align:center">FECHA RECEPCION DATA</th>
            <th style="background: black;color:white;text-align:center">Fecha Requerida</th>
            <th style="background: black;color:white;text-align:center">Nro. Despacho</th>
            <th style="background: black;color:white;text-align:center">NV Emision</th>
            <th style="background: black;color:white;text-align:center">Nro. LPN</th>
            <th style="background: black;color:white;text-align:center">Producto</th>
            <th style="background: #bf000b;color:white;text-align:center">ESTADO GN7 QAYARIX</th>
            <th style="background: #bf000b;color:white;text-align:center">MOTIVO</th>
            <th style="background: #bf000b;color:white;text-align:center">FECHA ENTREGA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detalle as $item)
        <tr>
            <td>{{$item->fecha_despacho}}</td>
            <td>{{$item->fecha_requerida}}</td>
            <td>{{$item->alt_code1}}</td>
            <td>{{$item->seg_code}}</td>
            <td>{{$item->client_barcode}}</td>
            <td>{{$item->sku_code}}</td>
            <td>{{$item->status}}</td>
            <td>{{$item->motive}}</td>
            <td>{{$item->fecha_entrega}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

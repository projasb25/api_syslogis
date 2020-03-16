<?php

namespace App\Models\Services;

use App\Http\Requests\Pedido\grabarImagen;
use Illuminate\Http\Request;
use Log;
use Intervention\Image\Facades\Image;

class PedidoService
{
    /**
     * @var variable
    protected $variable;
     */

    public function __construct()
    {
        // code
    }

    public function grabarImagen(grabarImagen $request)
    {
        $image = $request->file('imagen');
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        $destination_path = public_path('/imagenes/thumbnail/');
        $rezise_image = Image::make($image->getRealPath());
        $rezise_image->resize(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destination_path . '/' . $image_name);
        $destination_path = public_path('/imagenes');
        $image->move($destination_path, $image_name);
        return response()->json($image_name);
        // $filename = "test.jpg";
        // $path = $request->file('imagen')->move(public_path("/"), $filename);
        // $photourl = url('/' . $filename);
    }
}

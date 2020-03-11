<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Location\Coordinate;
use Location\Distance\Vincenty;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'conductor'], function () {
    Route::get('/ofertas', 'ConductorController@listarOfertas');
    Route::post('/actualizarEstado', 'ConductorController@actualizarEstado');
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'envio'], function () {
    Route::post('/aceptar', 'EnviosController@aceptar');
    Route::get('/rutas/{idofertaenvio}', 'EnviosController@listarRutas');
});

Route::get('/test', function () {
    $coordinate1 = new Coordinate(32.9697, -96.80322); // Mauna Kea Summit
    $coordinate2 = new Coordinate(29.46786, -98.53506); // Haleakala Summit
    $calculator = new Vincenty();
    #32.9697, -96.80322 -- 29.46786, -98.53506
    #8.2414957921875
    echo $calculator->getDistance($coordinate1, $coordinate2); // returns 128130.850 (meters; ≈128 kilometers)
    // function distance($lat1, $lon1, $lat2, $lon2, $unit)
    // {
    //     if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    //         return 0;
    //     } else {
    //         $theta = $lon1 - $lon2;
    //         $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    //         $dist = acos($dist);
    //         $dist = rad2deg($dist);
    //         $miles = $dist * 60 * 1.1515;
    //         $unit = strtoupper($unit);

    //         if ($unit == "K") {
    //             return ($miles * 1.609344);
    //         } else if ($unit == "N") {
    //             return ($miles * 0.8684);
    //         } else {
    //             return $miles;
    //         }
    //     }
    // }

    // echo distance(-12.179256, -76.9949845, -12.1675125, -76.9201161, "M") . " Miles<br>";
    // echo distance(-12.179256, -76.9949845, -12.1675125, -76.9201161, "K") . " Kilometers<br>";
    // echo distance(-12.179256, -76.9949845, -12.1675125, -76.9201161, "N") . " Nautical Miles<br>";
});

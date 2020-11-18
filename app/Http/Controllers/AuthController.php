<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Entities\Driver;
use App\Models\Repositories\ConductorRepository;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper as Res;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private $conductorRepo;

    public function __construct(ConductorRepository $conductorRepository)
    {
        $this->conductorRepo = $conductorRepository;
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $username = request()->get('email');
            $password = request()->get('password');

            Config::set('auth.providers.users.model', \App\Models\Entities\Driver::class);

            $driver = Driver::where('email', $username)->whereNotIn('status', 'ELIMINADO')->first();

            if (!$driver) {
                throw new CustomException(['Usuario no existe.', 2000], 401);
            }
            if (!Hash::check($password, $driver->password)) {
                throw new CustomException(['Credenciales incorrectas.', 2001], 401);
            }
            
            $token = auth()->login($driver);

            return Res::success([
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'email' => $driver->email,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

            return response()->json($response);
        } catch (CustomException $e) {
            Log::warning('Iniciar Session error', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Iniciar Session error', ['exception' => $e->getMessage(), 'request' => request()->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $conductor = auth()->user()->idconductor;
        $this->conductorRepo->ActualizarEstado($conductor, 'NO DISPONIBLE');

        auth()->logout();

        return response()->json([
            'success' => true,
            'data' => [
                'mensaje' => 'Successfully logged out'
            ]
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

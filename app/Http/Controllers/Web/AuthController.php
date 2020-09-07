<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Repositories\ConductorRepository;
use App\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private $conductorRepo;

    public function __construct(ConductorRepository $conductorRepository)
    {
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
            $data = request()->get('data');
            $query = DB::select("CALL SP_AUTHENTICATE(?)", [$data['usr']]);

            if (!$query) {
                throw new CustomException(['Usuario no existe.', 2000], 401);
            }
            if (!Hash::check($data['password'], $query[0]->password)) {
                throw new CustomException(['Credenciales incorrectas.', 2001], 401);
            }

            $user = User::where('id_user', $query[0]->id_user)->first();

            $token = auth()->login($user);

            return Res::success([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (CustomException $e) {
            Log::warning('Iniciar Session error', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Iniciar Session error', ['exception' => $e->getMessage(), 'request' => request()->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
    }

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

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function me()
    {
        $user = auth()->user();
        return Res::success([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

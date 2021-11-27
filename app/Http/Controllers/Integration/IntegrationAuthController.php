<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ChangeRequest;
use App\Models\Entities\IntegrationUser;
use App\Models\Repositories\ConductorRepository;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;

class IntegrationAuthController extends Controller
{
    private $conductorRepo;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'properties']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            Config::set('auth.providers.users.model', \App\Models\Entities\IntegrationUser::class);

            $data = request()->get('data');
            $user = IntegrationUser::where('integration_username', $data['usuario'])->whereNotIn('status', ['ELIMINADO'])->first();
            if (!$user) {
                throw new CustomException(['Usuario no existe.', 2000], 401);
            }
            if (!Hash::check($data['clave'], $user->integration_password)) {
                throw new CustomException(['Credenciales incorrectas.', 2001], 401);
            }

            auth()->factory()->setTTL(null);
            $token = auth()->login($user);

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer'
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
        $query = DB::select("CALL SP_AUTHENTICATE(?)", [$user->username]);
        return Res::success([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'rol_name' => $query[0]->role_name
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

    public function change(ChangeRequest $request)
    {
        try {
            $user = auth()->user();

            $req = $request->all();
            if (!isset($req['id_corporation'])) {
                $sesion_data = $user->getIdentifierData();
                $id_coporation = $sesion_data['current_corp'];
            } else {
                $id_coporation = $req['id_corporation'];
            }

            auth()->logout();
            $validate = DB::select("CALL SP_VALIDATE_ORGUSER(?,?,?)", [$user->id_user, $id_coporation, $request->get('id_organization')]);
            if (!$validate) {
                throw new CustomException(['Usuario no tiene permisos.', 2000], 401);
            }

            $token = auth()->claims(
                ['current_org' => $request->get('id_organization'), 'current_corp' => $id_coporation]
            )->login($user);
            // $token = auth()->login($user);

            return Res::success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (CustomException $e) {
            Log::warning('Change entity error', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Change entity error', ['exception' => $e->getMessage(), 'request' => request()->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
    }

    public function properties()
    {
        $query = DB::table('properties')->whereIn('name', ['sys_company_name', 'sys_company_img', 'sys_company_color_primary', 'sys_company_color_secondary'])->get();
        return Res::success([
            $query
        ]);
    }
}

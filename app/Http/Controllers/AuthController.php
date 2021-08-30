<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Models\Repositories\ConductorRepository;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper as Res;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    private $conductorRepo;

    public function __construct(ConductorRepository $conductorRepository)
    {
        $this->conductorRepo = $conductorRepository;
        $this->middleware('auth:api', ['except' => ['login','register','properties']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            Config::set('auth.providers.users.model', \App\User::class);

            $data = request()->get('data');
            $query = DB::select("CALL SP_AUTHENTICATE(?,?)", [$data['usr'],$data['origin']]);

            if (!$query) {
                throw new CustomException(['Usuario no existe.', 2000], 401);
            }
            if (!Hash::check($data['password'], $query[0]->password)) {
                throw new CustomException(['Credenciales incorrectas.', 2001], 401);
            }
            $user = User::where('id_user', $query[0]->id_user)->first();
            $token = auth()->login($user);

            $data = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'type' => $user->type,
                'token' => $token,
                'phone' => $user->phone,
                'user_email' => $user->user_email,
                'doc_type' => $user->doc_type,
                'doc_number' => $user->doc_number,
                'rol_name' => $query[0]->role_name,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ];

            if ($user->type == 'DRIVER') {
                array_push($data,['plate_number' => $query[0]->plate_number]);
            };

            return Res::success($data);

        } catch (CustomException $e) {
            Log::warning('Iniciar Session error', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Iniciar Session error', ['exception' => $e->getMessage(), 'request' => request()->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
    }

    public function register()
    {
        try {
            Config::set('auth.providers.users.model', \App\User::class);

            $data = request()->get('data');
            $data['password'] = Hash::make($data['password']);
            $query = DB::select("CALL SP_INS_CLIENT(?,?,?,?,?,?,?,?,?,?,?,?)", [
                0,
                $data['username'],
                $data['first_name'],
                $data['last_name'],
                $data['doc_type'],
                $data['doc_number'],
                $data['user_email'],
                $data['phone'],
                $data['password'],
                'ACTIVO',
                $data['type'],
                'APP',
            ]);

            $user = User::where('id_user', $query[0]->id_user)->first();
            $token = auth()->login($user);

            return Res::success([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'user_email' => $user->user_email,
                'doc_type' => $user->doc_type,
                'doc_number' => $user->doc_number,
                'type' => $user->type,
                'token' => $token,
                // 'rol_name' => $query[0]->role_name,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (CustomException $e) {
            Log::warning('Iniciar Session error', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            if ((int) $e->getCode() >= 60000) {
                Log::warning('Iniciar Session error', ['exception' => $e->errorInfo[2], 'request' => request()->all()]);
                return Res::error([$e->errorInfo[2], (int) $e->getCode()], 400);
            }
            Log::warning('Iniciar Session error', ['exception' => $e->getMessage(), 'request' => request()->all()]);
            return Res::error(['Unxpected error', 3000], 400);
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
        try {
            $res = [];
            $user = auth()->user();
            $query = DB::select("CALL SP_AUTHENTICATE(?,?)", [$user->username,'WEB']);
            if (!$query) {
                throw new CustomException(['Token invalido.', 2000], 401);
            }
            $roles = DB::select("CALL SP_SEL_ROLEAPPLICATION(?)", [$query[0]->id_role]);
            foreach ($roles as $rol) {
                array_push($res, [
                    "application" => $rol->name,
                    'description' =>  $rol->app_desc,
                    'tag' => $rol->tag,
                    "path" => $rol->path,
                    "insert" => ($user->id_user === 1 ) ? 1 : $rol->insert,
                    "view" => ($user->id_user === 1 ) ? 1 : $rol->view,
                    "update" => ($user->id_user === 1 ) ? 1 : $rol->modify,
                    "delete" => ($user->id_user === 1 ) ? 1 : $rol->delete
                ]);
            }
        } catch (CustomException $e) {
            Log::warning('Validate token', ['expcetion' => $e->getData()[0], 'request' => request()->all()]);
            return Res::error($e->getData(), $e->getCode());
        }

        return Res::success([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'rol_name' => $query[0]->role_name,
            'menu' => $res,
        ]);
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

    public function properties()
    {
        $query = DB::table('properties')->whereIn('name', ['sys_company_name', 'sys_company_img', 'sys_company_color_primary', 'sys_company_color_secondary'])->get();
        return Res::success([
            $query
        ]);
    }
}

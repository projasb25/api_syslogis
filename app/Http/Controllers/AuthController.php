<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
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
            $username = request()->get('email');
            $password = request()->get('password');
            $hashed_pass = hash('sha256', $password . env('TOKEN_SECRET'));

            # Validamos que usuario y contraseña sean correctas
            $user = User::where([
                ['correo', '=', $username],
                ['contrasena', '=', $hashed_pass]
            ])->first();

            if (!$user) {
                $response = [
                    'success' => false,
                    'error' => [
                        'mensaje' => 'Credenciales incorrectas.',
                        'code' => 2000
                    ]
                ];
                return response()->json($response, 401);
            }

            $token = auth()->login($user);
            $response = [
                'success' => true,
                'data' => [
                    'token' => $token,
                    'nombre' => $user->nombre,
                    'email' => $user->correo,
                    'code' => 2000
                ]
            ];
            return response()->json($response);
        } catch (\Throwable $th) {
            dd($th);
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

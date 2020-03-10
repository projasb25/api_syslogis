<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (
            $exception instanceof TokenExpiredException ||
            $exception instanceof TokenBlacklistedException ||
            $exception instanceof JWTException ||
            $exception instanceof AuthenticationException
        ) {

            $response = [
                'success' => false,
                'error' => [
                    'mensaje' => 'Invalid token.',
                    'code' => 3013
                ]
            ];
            return response()->json($response, 401);
        }

        if (
            $exception instanceof Exception 
        ) {

            $response = [
                'success' => false,
                'error' => [
                    'mensaje' => 'Ups! Something wrong.',
                    'code' => 3000
                ]
            ];
            return response()->json($response, 500);
        }

        return parent::render($request, $exception);
    }
}
